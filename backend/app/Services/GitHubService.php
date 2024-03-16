<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Service class to interact with GitHub API and fetch repository data.
 */
class GitHubService
{
    private const BASE_URL = 'https://api.github.com/search/repositories';
    private const MAX_RESULTS_PER_PAGE = 100; // GitHub's maximum allowed results per page
    private const TOTAL_MAX_RESULTS = 500; // The maximum number of results we want to fetch
    private const RETRY_DELAY_SECONDS = 2; // Delay between retries on failure

    private $client;

    public function __construct()
    {
        // Initializing Guzzle HTTP client with GitHub's base URL
        $this->client = new Client(['base_uri' => self::BASE_URL]);
    }

    /**
     * Searches GitHub repositories based on the given criteria.
     *
     * @param string $topic The topic to search for.
     * @param string|null $search Additional search keywords.
     * @param string $sort The field to sort by.
     * @param string $order Sort order, 'asc' or 'desc'.
     * @param int $perPage Number of results per page.
     * @param int $page The current page number.
     * @return array The search result and pagination data.
     */
    public function searchRepositories(string $topic = 'php', ?string $search = null, string $sort = 'name', string $order = 'asc', int $perPage = 10, int $page = 1): array
    {
        $initialResponse = $this->fetchPage($topic, $search, 1);
        $totalAvailable = min($initialResponse['total_count'] ?? 0, self::TOTAL_MAX_RESULTS);
        $allItems = collect($initialResponse['items']);

        // Fetch additional pages if more results are available
        if ($totalAvailable > self::MAX_RESULTS_PER_PAGE) {
            $totalPages = min(ceil($totalAvailable / self::MAX_RESULTS_PER_PAGE), 5);
            $allItems = $this->fetchAllPages($topic, $search, $totalPages, $allItems);
        }

        if ($allItems->isEmpty()) {
            return $this->handleEmptyResponse();
        }

        // Convert date strings to timestamps to facilitate sorting
        $allItems = $this->formatDates($allItems)->unique('id');

        // Sorting should happen after fetching all records
        $sorted = $this->sortRepositories($allItems, $sort, $order)->slice(0, self::TOTAL_MAX_RESULTS);

        // Paginating the sorted items
        $paginated = $this->paginate($sorted, $perPage, $page);

        return [
            'data' => $paginated->values()->all(),
            'total' => $sorted->count(),
            'per_page' => $perPage,
            'current_page' => $page,
        ];
    }

    /**
     * Fetches all pages of results asynchronously.
     *
     * @param string $topic The topic to search for.
     * @param string|null $search Additional search keywords.
     * @param int $totalPages The total number of pages to fetch.
     * @param Collection $collectedItems The collection of items fetched so far.
     * @return Collection The merged collection of all fetched items.
     */
    private function fetchAllPages($topic, $search, $totalPages, $collectedItems)
    {
        $promises = [];
        for ($i = 2; $i <= $totalPages; $i++) {
            $promises[] = $this->fetchPageAsync($topic, $search, $i);
        }

        $results = Promise\Utils::unwrap($promises);

        foreach ($results as $response) {
            $data = json_decode($response->getBody()->getContents(), true);
            $collectedItems = $collectedItems->merge($data['items']);
        }

        return $collectedItems;
    }

    /**
     * Fetches a single page of results asynchronously.
     *
     * @param string $topic The topic to search for.
     * @param string|null $search Additional search keywords.
     * @param int $page The page number to fetch.
     * @return Promise The promise of the async HTTP request.
     */
    private function fetchPageAsync($topic, $search, $page)
    {
        return $this->client->getAsync($this->buildUrl($topic, $search, $page))
            ->then(
                function ($response) {
                    return $response;
                },
                function ($exception) use ($topic, $search, $page) {
                    if ($exception instanceof GuzzleException) {
                        sleep(self::RETRY_DELAY_SECONDS);
                        return $this->fetchPageAsync($topic, $search, $page);
                    }

                    throw $exception;
                }
            );
    }

    /**
     * Fetches a single page of results synchronously.
     *
     * @param string $topic The topic to search for.
     * @param string|null $search Additional search keywords.
     * @param int $page The page number to fetch.
     * @return array The decoded JSON response as an array.
     */
    private function fetchPage($topic, $search, $page)
    {
        $response = $this->client->get($this->buildUrl($topic, $search, $page));
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Builds the URL for the GitHub API request.
     *
     * @param string $topic The topic to search for.
     * @param string|null $search Additional search keywords.
     * @param int $page The page number.
     * @return string The full URL for the API request.
     */
    private function buildUrl(string $topic, ?string $search, int $page): string
    {
        $params = "q=topic:$topic";
        if ($search) {
            $params .= "+$search in:name,description";
        }
        $params .= "&per_page=" . self::MAX_RESULTS_PER_PAGE . "&page=$page";

        return self::BASE_URL . '?' . $params;
    }

    /**
     * Formats date strings in the item data to timestamps for easier sorting.
     *
     * @param Collection $items The collection of items to format.
     * @return Collection The collection with formatted date timestamps.
     */
    private function formatDates(Collection $items): Collection
    {
        return $items->map(function ($item) {
            $item['updated_at_timestamp'] = Carbon::parse($item['updated_at'])->timestamp;
            $item['pushed_at_timestamp'] = Carbon::parse($item['pushed_at'])->timestamp;
            return $item;
        });
    }

    /**
     * Sorts the repository items based on the specified field and order.
     *
     * @param Collection $repositories The collection of repository items to sort.
     * @param string $sort The field to sort by.
     * @param string $order The order to sort in, 'asc' or 'desc'.
     * @return Collection The sorted collection.
     */
    private function sortRepositories(Collection $repositories, string $sort, string $order): Collection
    {
        switch ($sort) {
            case 'popularity':
                $key = 'stargazers_count';
                break;
            case 'activity':
                $key = 'updated_at_timestamp';
                break;
            case 'name':
            default:
                $key = 'name';
                break;
        }

        return $order === 'asc' ? $repositories->sortBy($key) : $repositories->sortByDesc($key);
    }

    /**
     * Paginates the given items into the specified page size.
     *
     * @param Collection $items The collection of items to paginate.
     * @param int $perPage The number of items per page.
     * @param int $page The page number to retrieve.
     * @return Collection The paginated collection for the specified page.
     */
    private function paginate(Collection $items, int $perPage, int $page): Collection
    {
        return $items->forPage($page, $perPage);
    }

    /**
     * Returns a standardized response for when no data is found.
     *
     * @return array The standardized error response array.
     */
    private function handleEmptyResponse(): array
    {
        return [
            'error' => true,
            'message' => 'No data was returned from GitHub. This may be due to rate limiting or other API restrictions.',
            'data' => [],
            'total' => 0,
            'per_page' => 0,
            'current_page' => 0,
        ];
    }
}
