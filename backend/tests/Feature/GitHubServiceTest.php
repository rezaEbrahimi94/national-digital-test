<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\GitHubService;
use Illuminate\Support\Facades\Http;

class GitHubServiceTest extends TestCase
{
    public function it_fetches_repositories_successfully()
    {
        // Assuming each response has 100 items and making 5 requests to get 500 items
        $mockResponse = ['items' => array_fill(0, 100, ['id' => 1, 'name' => 'Sample Repo'])];
        Http::fake([
            '*' => Http::response($mockResponse, 200),
        ]);

        $service = new GitHubService();
        $response = $service->searchRepositories();

        $this->assertIsArray($response);
        $this->assertEquals(500, $response['total']); // Expecting 500 
    }

    public function it_handles_rate_limit_errors_gracefully()
    {
        // Mock the HTTP facade to simulate a rate limit error response
        Http::fake([
            '*' => Http::response(['message' => 'API rate limit exceeded'], 403),
        ]);
    
        $service = new GitHubService();
        $response = $service->searchRepositories();
    
        // Ensure the 'error' key is set and true
        $this->assertTrue(isset($response['error']));
        $this->assertTrue($response['error']); // Specifically check for boolean true
        $this->assertStringContainsString('rate limit exceeded', $response['message']);
    }
    
    public function it_handles_network_errors_gracefully()
    {
        // Simulate network error. Method depends on how service is structured.

        $response = $this->gitHubService->searchRepositories();

        // Assert that the response structure matches what's expected when a network error occurs.
        $this->assertTrue($response['error']);
        $this->assertEquals('A network error occurred. Please try again later.', $response['message']);
    }

    public function it_makes_a_real_request_and_validates_the_response_structure()
    {
        $service = new GitHubService();
        $response = $service->searchRepositories(topic: 'php', perPage: 2, page: 1); // Keeping it small to avoid rate limiting

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('total', $response);
        $this->assertArrayHasKey('per_page', $response);
        $this->assertArrayHasKey('current_page', $response);

        // Validate structure of the first item in the 'data' array
        $firstItem = $response['data'][0] ?? null;
        $this->assertNotNull($firstItem);
        $this->assertArrayHasKey('id', $firstItem);
        $this->assertArrayHasKey('node_id', $firstItem);
        $this->assertArrayHasKey('name', $firstItem);
        $this->assertArrayHasKey('full_name', $firstItem);
        $this->assertArrayHasKey('owner', $firstItem);
        $this->assertArrayHasKey('private', $firstItem);

    }

}
