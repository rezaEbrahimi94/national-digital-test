<?php

namespace App\Responders;

use Illuminate\Http\JsonResponse;

class ListRepositoriesResponder
{
    public function __invoke($data): JsonResponse
    {
        // Map over each repository and only include the specified fields
        $modifiedData = array_map(function ($repository) {
            return array_intersect_key($repository, array_flip([
                'id', 'name', 'full_name', 'html_url', 'language', 'updated_at', 'pushed_at', 'stargazers_count'
            ]));
        }, $data['data']);

        // Update the 'data' field with the modified data
        $data['data'] = $modifiedData;

        return response()->json($data);
    }
}
