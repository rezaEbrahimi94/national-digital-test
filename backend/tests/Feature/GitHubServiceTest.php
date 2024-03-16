<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\GitHubService;
use Illuminate\Support\Facades\Http;

/**
 * Unit tests for GitHubService class.
 */
class GitHubServiceTest extends TestCase
{
    /**
     * Test successful fetching of repositories.
     */
    public function test_it_fetches_repositories_successfully()
    {
        // Prepare a mock response to simulate GitHub API's paginated response
        $mockResponse = ['total_count' => 500, 'items' => array_fill(0, 100, ['id' => 1, 'name' => 'Sample Repo'])];
        Http::fake([
            '*' => Http::response($mockResponse, 200),
        ]);

        $service = new GitHubService();
        $response = $service->searchRepositories();

        // Assert that the response is an array and contains the expected number of items
        $this->assertIsArray($response);
        $this->assertEquals(500, $response['total']);
    }

    /**
     * Test the handling of rate limit errors gracefully.
     */
    public function test_it_handles_rate_limit_errors_gracefully()
    {
        // Simulate a rate limit error from the GitHub API
        Http::fake([
            '*' => Http::response(['error' => 'API rate limit exceeded'], 403),
        ]);

        $service = new GitHubService();
        $response = $service->searchRepositories();

        // Check that the service handles the error gracefully and returns an array
        $this->assertIsArray($response);
        $this->assertNotEmpty($response); // Check that the service provides an error response
    }

    /**
     * Test the handling of network errors gracefully.
     */
    public function test_it_handles_network_errors_gracefully()
    {
        // Simulate a network error
        Http::fake(function() {
            return Http::response(['error' => 'Network error'], 500);
        });

        $service = new GitHubService();
        $response = $service->searchRepositories();

        // Check that the service handles network errors gracefully
        $this->assertIsArray($response);
        $this->assertNotEmpty($response); // Check that the service provides an error response
    }

    /**
     * Test making a real request and validating the response structure.
     */
    public function test_it_makes_a_real_request_and_validates_the_response_structure()
    {
        $service = new GitHubService();
        $response = $service->searchRepositories(topic: 'php', perPage: 2, page: 1);

        // Assert the structure of the response matches the expected format
        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('total', $response);
        $this->assertArrayHasKey('per_page', $response);
        $this->assertArrayHasKey('current_page', $response);

        // Ensure the first item in the response has the expected keys
        $firstItem = $response['data'][0] ?? null;
        $this->assertNotNull($firstItem);
        $this->assertArrayHasKey('id', $firstItem);
        $this->assertArrayHasKey('name', $firstItem);
        $this->assertArrayHasKey('full_name', $firstItem);
    }
}
