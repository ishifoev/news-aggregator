<?php

namespace App\Repositories;

use App\Contracts\ArticleRepositoryInterface;
use App\Models\Article;
use App\Models\Source;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function getArticles(array $filters): LengthAwarePaginator
    {
        return Article::with('source') // Eager loading 'source' for optimized queries
            ->when(isset($filters['keyword']), function ($query) use ($filters) {
                $query->where('title', 'like', '%'.$filters['keyword'].'%')
                    ->orWhere('content', 'like', '%'.$filters['keyword'].'%');
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

    public function saveArticlesWithSources(array $articles): void
    {
        DB::transaction(function () use ($articles) {
            foreach ($articles as $articleData) {
                $source = Source::updateOrCreate(
                    ['name' => $articleData['source']],
                    ['metadata' => json_encode(['category' => $articleData['category']])]
                );

                Article::updateOrCreate(
                    ['title' => $articleData['title']],
                    [
                        'content' => $articleData['content'],
                        'author' => $articleData['author'],
                        'published_at' => $articleData['published_at'],
                        'source_id' => $source->id,
                        'category' => $articleData['category'],
                        'url' => $articleData['url'],
                    ]
                );
            }
        });

        Log::info('Articles and sources saved successfully', ['count' => count($articles)]);
    }

    public function findArticleById(int $id): Article
    {
        return Article::findOrFail($id);
    }
}
