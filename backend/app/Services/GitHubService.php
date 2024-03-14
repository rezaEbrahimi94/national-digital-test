<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;

class GitHubService
{
    private const BASE_URL = 'https://api.github.com/search/repositories';
    private const MAX_RESULTS_PER_PAGE = 100;
    private const TOTAL_MAX_RESULTS = 500; // Desired total results to fetch

    /**
     * Search GitHub repositories by topic with optional search term, sorting, and pagination.
     */
    public function searchRepositories(string $topic = 'php', ?string $search = null, string $sort = 'name', string $order = 'asc', int $perPage = 10, int $page = 1): array
    {
        // Calculate how many pages to fetch based on the perPage parameter and desired total max results
        $totalPagesToFetch = (int) ceil(self::TOTAL_MAX_RESULTS / self::MAX_RESULTS_PER_PAGE);
        $client = new Client(['base_uri' => self::BASE_URL]);
        $promises = [];

        for ($i = 0; $i < $totalPagesToFetch; $i++) {
            $promises[] = $client->getAsync($this->buildUrl($topic, $search, $i + 1));
        }

        try {
            $results = Utils::settle($promises)->wait();
        } catch (GuzzleException $e) {
            return $this->handleNetworkError($e);
        }

        $allItems = collect();
        foreach ($results as $result) {
            if ($result['state'] === 'fulfilled' && isset($result['value'])) {
                $response = json_decode($result['value']->getBody()->getContents(), true);
                $allItems = $allItems->merge(collect($response['items']));
            } elseif ($result['state'] === 'rejected') {
                continue; 
            }
        }

        if ($allItems->isEmpty()) {
            return $this->handleEmptyResponse();
        }

        $sorted = $this->sortRepositories($allItems, $sort, $order);
        $paginated = $this->paginate($sorted, $perPage, $page);

        return [
            'data' => $paginated->values()->all(),
            'total' => $allItems->count(), 
            'per_page' => $perPage,
            'current_page' => $page,
        ];
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

    private function sortRepositories(Collection $repositories, string $sort, string $order): Collection
    {
        $key = $sort === 'popularity' ? 'stargazers_count' : ($sort === 'activity' ? 'pushed_at' : 'name');
        return $repositories->sortBy($key, SORT_REGULAR, $order !== 'asc');
    }

    private function paginate(Collection $items, int $perPage, int $page): Collection
    {
        return $items->forPage($page, $perPage);
    }

    private function handleNetworkError(GuzzleException $e): array
    {
        return [
            'error' => true,
            'message' => 'A network error occurred. Please try again later.',
        ];
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
