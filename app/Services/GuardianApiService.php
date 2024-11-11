<?php

namespace App\Services;

use App\Contracts\ArticleFetcherInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuardianApiService implements ArticleFetcherInterface
{
    public function fetchArticles(): array
    {
        try {
            return Cache::remember('guardian_articles', 0, function () {
                $response = Http::withHeaders(['Accept' => 'application/json'])
                    ->get(env('GUARDIAN_API_URL'), ['api-key' => env('GUARDIAN_API_KEY'), 'show-fields' => 'all']);

                if ($response->successful()) {
                    return $this->processArticles($response->json()['response']['results']);
                }

                Log::warning('Failed to fetch articles from Guardian API', ['status' => $response->status()]);

                return [];
            });
        } catch (\Exception $e) {
            Log::error('Error fetching articles from Guardian API', ['error' => $e->getMessage()]);

            return [];
        }
    }

    private function processArticles(array $articles): array
    {
        return array_map(function ($article) {
            return [
                'title' => htmlspecialchars($article['webTitle'] ?? 'Untitled'),
                'content' => htmlspecialchars($article['fields']['bodyText'] ?? 'No content available'),
                'author' => $article['fields']['byline'] ?? 'Unknown',
                'published_at' => now(),
                'source' => 'The Guardian',
                'category' => $article['sectionName'] ?? 'general',
                'url' => $article['webUrl'] ?? null,
            ];
        }, $articles);
    }
}
