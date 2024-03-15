<?php

namespace App\Responders;

use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ListRepositoriesResponder
{
    public function __invoke($data): JsonResponse
    {
        // Map over each repository and format the date fields
        $modifiedData = array_map(function ($repository) {
            $repository['updated_at'] = Carbon::parse($repository['updated_at'])->toDateTimeString();
            $repository['pushed_at'] = Carbon::parse($repository['pushed_at'])->toDateTimeString();

            return array_intersect_key($repository, array_flip([
                'id', 'name', 'full_name', 'html_url', 'language', 'updated_at', 'pushed_at', 'stargazers_count'
            ]));
        }, $data['data']);

        // Update the 'data' field with the modified data
        $data['data'] = $modifiedData;

        return response()->json($data);
    }
}
