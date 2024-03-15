<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\GitHubService;
use Illuminate\Support\Facades\Http;

class GitHubServiceTest extends TestCase
{
    public function test_it_fetches_repositories_successfully()
    {
        // Assuming each response has 100 items and making 5 requests to get 500 items
        $mockResponse = ['total_count' => 500, 'items' => array_fill(0, 100, ['id' => 1, 'name' => 'Sample Repo'])];
        Http::fake([
            '*' => Http::response($mockResponse, 200),
        ]);

        $service = new GitHubService();
        $response = $service->searchRepositories();

        $this->assertIsArray($response);
        $this->assertEquals(500, $response['total']);
    }

public function test_it_handles_rate_limit_errors_gracefully()
{
    Http::fake([
        '*' => Http::response(['error' => 'API rate limit exceeded'], 403),
    ]);

    $service = new GitHubService();
    $response = $service->searchRepositories();

    // Check if the response indicates an error
    $this->assertIsArray($response);
    $this->assertNotEmpty($response); // Assuming an error will still return some data
}

public function test_it_handles_network_errors_gracefully()
{
    Http::fake(function() {
        return Http::response(['error' => 'Network error'], 500);
    });

    $service = new GitHubService();
    $response = $service->searchRepositories();

    // Check if the response indicates an error
    $this->assertIsArray($response);
    $this->assertNotEmpty($response); // Assuming an error will still return some data
}



    public function test_it_makes_a_real_request_and_validates_the_response_structure()
    {
        $service = new GitHubService();
        $response = $service->searchRepositories(topic: 'php', perPage: 2, page: 1);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('total', $response);
        $this->assertArrayHasKey('per_page', $response);
        $this->assertArrayHasKey('current_page', $response);

        $firstItem = $response['data'][0] ?? null;
        $this->assertNotNull($firstItem);
        $this->assertArrayHasKey('id', $firstItem);
        $this->assertArrayHasKey('name', $firstItem);
        $this->assertArrayHasKey('full_name', $firstItem);
    }
}
