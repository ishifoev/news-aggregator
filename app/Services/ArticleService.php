<?php

namespace App\Services;

use App\Contracts\ArticleServiceInterface;
use App\Contracts\ArticleRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Article;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ArticleService implements ArticleServiceInterface
{
    protected ArticleRepositoryInterface $articleRepository;

    public function __construct(ArticleRepositoryInterface $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    /**
     * Get a list of articles with optional filters and pagination.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     * @throws \Exception
     */
    public function getArticles(array $filters): LengthAwarePaginator
    {
        try {
            Log::info('Fetching articles with filters', $filters);
            $cacheKey = 'articles_' . md5(json_encode($filters));
            return Cache::remember($cacheKey, 300, function () use ($filters) {
                return $this->articleRepository->getArticles($filters);
            });
        } catch (\Exception $e) {
            Log::error('Failed to fetch articles', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get the details of a single article by its ID.
     *
     * @param int $id
     * @return Article
     * @throws \Exception
     */
    public function getArticleById(int $id): Article
    {
        try {
            Log::info("Fetching article with ID: {$id}");
            return Cache::remember("article_{$id}", 300, function () use ($id) {
                return $this->articleRepository->findArticleById($id);
            });
        } catch (ModelNotFoundException $e) {
            Log::warning("Article with ID {$id} not found");
            throw $e;
        } catch (\Exception $e) {
            Log::error("Error fetching article with ID {$id}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
