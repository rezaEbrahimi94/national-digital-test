<?php

namespace App\Responders;

use Illuminate\Http\JsonResponse;

class ListRepositoriesResponder
{
    public function __invoke($data): JsonResponse
    {
        return response()->json($data);
    }
}
