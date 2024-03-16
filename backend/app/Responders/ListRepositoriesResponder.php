<?php

namespace App\Responders;

use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

/**
 * Responder class responsible for formatting and returning the repository data.
 */
class ListRepositoriesResponder
{
    /**
     * Invokes the responder to process and format the repository data.
     *
     * @param array $data The original data retrieved from the GitHub API.
     * @return JsonResponse The formatted data as a JSON response.
     */
    public function __invoke($data): JsonResponse
    {
        // Map over each repository and format the date fields to a more readable format.
        $modifiedData = array_map(function ($repository) {
            // Use Carbon to parse and format the date fields for consistency and readability.
            $repository['updated_at'] = Carbon::parse($repository['updated_at'])->toDateTimeString();
            $repository['pushed_at'] = Carbon::parse($repository['pushed_at'])->toDateTimeString();

            // Select only the necessary fields to return, improving data transfer efficiency.
            return array_intersect_key($repository, array_flip([
                'id', 'name', 'full_name', 'html_url', 'language', 'updated_at', 'pushed_at', 'stargazers_count'
            ]));
        }, $data['data']);

        // Update the 'data' key with the modified repository data before returning.
        $data['data'] = $modifiedData;

        // Return the modified data as a JSON response, adhering to API standards.
        return response()->json($data);
    }
}
