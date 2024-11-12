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
        $chunkSize = 1000; // Adjust based on your memory and performance needs

        DB::transaction(function () use ($articles, $chunkSize) {
            Log::info('Starting bulk upsert operation', ['total_articles' => count($articles)]);

            foreach (array_chunk($articles, $chunkSize) as $index => $articleChunk) {
                Log::info("Processing chunk {$index}", ['chunk_size' => count($articleChunk)]);

                $sources = [];
                $sourceMap = []; // Map for storing source IDs
                $articlesToInsert = [];

                foreach ($articleChunk as $articleData) {
                    $sourceName = $articleData['source'];

                    // Check if the source has already been processed in this chunk
                    if (!isset($sourceMap[$sourceName])) {
                        $source = Source::updateOrCreate(
                            ['name' => $sourceName],
                            [
                                'api_url' => $articleData['url'],
                                'metadata' => json_encode(['category' => $articleData['category']]),
                                'updated_at' => now()
                            ]
                        );

                        $sourceMap[$sourceName] = $source->id;
                        $sources[] = [
                            'name' => $sourceName,
                            'api_url' => $articleData['url'],
                            'metadata' => json_encode(['category' => $articleData['category']]),
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }

                    $articlesToInsert[] = [
                        'title' => $articleData['title'],
                        'content' => $articleData['content'],
                        'author' => $articleData['author'],
                        'published_at' => $articleData['published_at'],
                        'source_id' => $sourceMap[$sourceName], // Use the source ID from the map
                        'category' => $articleData['category'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                if (!empty($sources)) {
                    Log::info('Upserting sources', ['chunk_index' => $index]);
                    Source::upsert($sources, ['name'], ['api_url', 'metadata', 'updated_at']);
                }

                // Perform bulk upserts for articles
                Log::info('Upserting articles', ['chunk_index' => $index]);
                if (!empty($articlesToInsert)) {
                    Article::upsert($articlesToInsert, ['title'], ['content', 'author', 'published_at', 'source_id', 'category', 'updated_at']);
                    Log::info("Chunk {$index} processed successfully");
                }

            }

            Log::info('Bulk upsert operation completed');
        });
    }



    public function findArticleById(int $id): Article
    {
        return Article::findOrFail($id);
    }
}
