<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListRepositoriesRequest;
use App\Actions\ListRepositoriesAction;
use App\Responders\ListRepositoriesResponder;

class RepositoryController extends Controller
{
    public function index(ListRepositoriesRequest $request, ListRepositoriesAction $action, ListRepositoriesResponder $responder)
    {
        return $responder($action($request));
    }

}
