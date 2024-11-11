<?php

namespace Tests\Unit;

use App\Services\NewsApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsApiServiceTest extends TestCase
{
    public function test_fetch_articles_from_news_api()
    {
        Http::fake([
            env('NEWS_API_URL').'*' => Http::response([
                'sources' => [
                    [
                        'title' => 'Test Article',
                        'description' => 'Test description',
                        'author' => 'Test Author',
                        'publishedAt' => now()->toDateTimeString(),
                        'source' => ['name' => 'Test Source'],
                        'url' => 'http://example.com',
                    ],
                ],
            ], 200),
        ]);

        $newsApiService = new NewsApiService;
        $articles = $newsApiService->fetchArticles();

        $this->assertNotEmpty($articles);
        $this->assertEquals('Untitled', $articles[0]['title']);
    }
}
