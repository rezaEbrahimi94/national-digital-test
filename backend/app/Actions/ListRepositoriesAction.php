<?php

namespace App\Actions;

use App\Services\GitHubService;
use App\Http\Requests\ListRepositoriesRequest;

class ListRepositoriesAction
{
    private $gitHubService;

    public function __construct(GitHubService $gitHubService)
    {
        $this->gitHubService = $gitHubService;
    }

    public function __invoke(ListRepositoriesRequest $request)
    {
        return $this->gitHubService->searchRepositories(
            search: $request->get('search', null),
            sort: $request->get('sort', 'name'),
            order: $request->get('order', 'asc'),
            perPage: $request->get('per_page', 10),
            page: $request->get('page', 1)
        );
    }
}
