<?php

namespace App\Services;

use App\Contracts\ArticleFetcherInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsApiService implements ArticleFetcherInterface
{
    public function fetchArticles(): array
    {
        try {
            return Cache::remember('newsapi_articles', 0, function () {
                $response = Http::withHeaders(['Accept' => 'application/json'])
                    ->get(env('NEWS_API_URL'), ['country' => env('NEWS_API_COUNTRY'), 'apiKey' => env('NEWS_API_KEY')]);

                if ($response->successful()) {
                    return $this->processArticles($response->json()['sources']);
                }

                Log::warning('Failed to fetch articles from NewsAPI', ['status' => $response->status()]);

                return [];
            });
        } catch (\Exception $e) {
            Log::error('Error fetching articles from NewsAPI', ['error' => $e->getMessage()]);

            return [];
        }
    }

    private function processArticles(array $articles): array
    {
        return array_map(function ($article) {
            return [
                'title' => htmlspecialchars($article['name'] ?? 'Untitled'),
                'content' => htmlspecialchars($article['description'] ?? 'No content available'),
                'author' => $article['author'] ?? 'Unknown',
                'published_at' => now(),
                'source' => $article['id'] ?? 'Unknown',
                'category' => $article['category'] ?? 'Unknown',
                'url' => $article['url'] ?? null,
            ];
        }, $articles);
    }
}
