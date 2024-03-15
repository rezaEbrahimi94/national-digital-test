<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListRepositoriesRequest;
use App\Actions\ListRepositoriesAction;
use App\Responders\ListRepositoriesResponder;

/**
 * @OA\Info(
 *   title="GitHub Repository API",
 *   version="1.0.0",
 *   description="API documentation for GitHub Repository API",
 *   contact={
 *     "email": "miaad.ebrahimi@gmail.com"
 *   }
 * )
 * @OA\Server(
 *   url="http://localhost:8000",
 *   description="Development Server"
 * )
 * @OA\Schema(
 *   schema="Repository",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=123),
 *   @OA\Property(property="name", type="string", example="Sample Repo"),
 *   @OA\Property(property="full_name", type="string", example="user/Sample-Repo"),
 *   @OA\Property(property="html_url", type="string", example="https://github.com/user/Sample-Repo"),
 *   @OA\Property(property="language", type="string", example="PHP"),
 *   @OA\Property(property="updated_at", type="string", example="2024-03-14T08:48:37Z"),
 *   @OA\Property(property="pushed_at", type="string", example="2024-03-13T16:22:57Z"),
 *   @OA\Property(property="stargazers_count", type="integer", example=100)
 * )
 */

class RepositoryController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/repos",
     *   summary="List GitHub repositories",
     *   tags={"Repositories"},
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/Repository")
     *       ),
     *       @OA\Property(property="total", type="integer"),
     *       @OA\Property(property="per_page", type="integer"),
     *       @OA\Property(property="current_page", type="integer")
     *     )
     *   ),
     *   @OA\Parameter(
     *     name="search",
     *     in="query",
     *     description="Search term",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     description="Sort field",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="order",
     *     in="query",
     *     description="Sort order",
     *     required=false,
     *     @OA\Schema(type="string", enum={"asc", "desc"})
     *   ),
     *   @OA\Parameter(
     *     name="per_page",
     *     in="query",
     *     description="Items per page",
     *     required=false,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page number",
     *     required=false,
     *     @OA\Schema(type="integer")
     *   )
     * )
     */
    public function index(ListRepositoriesRequest $request, ListRepositoriesAction $action, ListRepositoriesResponder $responder)
    {
        return $responder($action($request));
    }
}
