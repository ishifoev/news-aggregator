<?php

namespace App\Repositories;

use App\Contracts\ArticleRepositoryInterface;
use App\Models\Article;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function getArticles(array $filters): LengthAwarePaginator
    {
        return Article::with('source') // Eager loading 'source' for optimized queries
        ->when(isset($filters['keyword']), function ($query) use ($filters) {
            $query->where('title', 'like', '%' . $filters['keyword'] . '%')
                ->orWhere('content', 'like', '%' . $filters['keyword'] . '%');
        })
            ->when(isset($filters['date']), function ($query) use ($filters) {
                $query->whereDate('published_at', $filters['date']);
            })
            ->when(isset($filters['category']), function ($query) use ($filters) {
                $query->where('category', $filters['category']);
            })
            ->when(isset($filters['source']), function ($query) use ($filters) {
                $query->whereHas('source', function ($sourceQuery) use ($filters) {
                    $sourceQuery->where('name', $filters['source']);
                });
            })
            ->orderBy('published_at', 'desc')
            ->paginate(10);
    }


    public function findArticleById(int $id): Article
    {
        return Article::findOrFail($id);
    }
}
