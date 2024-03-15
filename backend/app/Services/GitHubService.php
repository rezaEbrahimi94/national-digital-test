<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class GitHubService
{
    private const BASE_URL = 'https://api.github.com/search/repositories';
    private const MAX_RESULTS_PER_PAGE = 100;
    private const TOTAL_MAX_RESULTS = 500;
    private const RETRY_DELAY_SECONDS = 2;

    private $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => self::BASE_URL]);
    }

    public function searchRepositories(string $topic = 'php', ?string $search = null, string $sort = 'name', string $order = 'asc', int $perPage = 10, int $page = 1): array
    {
        $initialResponse = $this->fetchPage($topic, $search, 1);
        $totalAvailable = min($initialResponse['total_count'] ?? 0, self::TOTAL_MAX_RESULTS);
        $allItems = collect($initialResponse['items']);

        if ($totalAvailable > self::MAX_RESULTS_PER_PAGE) {
            $totalPages = min(ceil($totalAvailable / self::MAX_RESULTS_PER_PAGE), 5);
            $allItems = $this->fetchAllPages($topic, $search, $totalPages, $allItems);
        }

        if ($allItems->isEmpty()) {
            return $this->handleEmptyResponse();
        }

        $allItems = $this->formatDates($allItems)->unique('id');

        // Sorting should happen after fetching all records
        $sorted = $this->sortRepositories($allItems, $sort, $order)->slice(0, self::TOTAL_MAX_RESULTS);

        $paginated = $this->paginate($sorted, $perPage, $page);

        return [
            'data' => $paginated->values()->all(),
            'total' => $sorted->count(),
            'per_page' => $perPage,
            'current_page' => $page,
        ];
    }

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

    private function fetchPage($topic, $search, $page)
    {
        $response = $this->client->get($this->buildUrl($topic, $search, $page));
        return json_decode($response->getBody()->getContents(), true);
    }

    private function buildUrl(string $topic, ?string $search, int $page): string
    {
        $params = "q=topic:$topic";
        if ($search) {
            $params .= "+$search in:name,description";
        }
        $params .= "&per_page=" . self::MAX_RESULTS_PER_PAGE . "&page=$page";

        return self::BASE_URL . '?' . $params;
    }

    private function formatDates(Collection $items): Collection
    {
        return $items->map(function ($item) {
            $item['updated_at_timestamp'] = Carbon::parse($item['updated_at'])->timestamp;
            $item['pushed_at_timestamp'] = Carbon::parse($item['pushed_at'])->timestamp;
            return $item;
        });
    }

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

    private function paginate(Collection $items, int $perPage, int $page): Collection
    {
        return $items->forPage($page, $perPage);
    }

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
