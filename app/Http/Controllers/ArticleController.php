<?php

namespace App\Http\Controllers;

use App\Contracts\ArticleServiceInterface;
use App\Http\Requests\ArticleFilterRequest;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    protected ArticleServiceInterface $articleService;

    public function __construct(ArticleServiceInterface $articleService)
    {
        $this->articleService = $articleService;
    }

    /**
     *
     *  Fetch articles with support for pagination and filtering.
     *
     * @param ArticleFilterRequest $request
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/v1/articles",
     *     tags={"Articles"},
     *     summary="Fetch articles with pagination and filters",
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="Filter articles by keyword",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter articles by published date",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter articles by category",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         description="Filter articles by source",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Too many requests. Please wait before trying again.")
     *         )
     *     )
     * )
     */
    public function index(ArticleFilterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $articles = $this->articleService->getArticles($validated);

        return response()->json($articles);
    }

    /**
     * /**
     *  Retrieve the details of a single article by its ID.
     *
     * @param int $id
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/v1/articles/{id}",
     *     tags={"Articles"},
     *     summary="Fetch a single article by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the article",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $article = $this->articleService->getArticleById($id);
        return response()->json($article);
    }
}
