<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchArticlesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_fetches_and_stores_articles()
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
            env('GUARDIAN_API_URL').'*' => Http::response([
                'response' => [
                    'results' => [
                        [
                            'webTitle' => 'Guardian Test Article',
                            'fields' => ['bodyText' => 'Guardian content', 'byline' => 'Guardian Author'],
                            'webUrl' => 'http://example.com/guardian',
                            'sectionName' => 'Guardian News',
                        ],
                    ],
                ],
            ], 200),
        ]);

        Artisan::call('articles:fetch');

        $this->assertDatabaseHas('articles', ['title' => 'Untitled']);
        $this->assertDatabaseHas('articles', ['title' => 'Guardian Test Article']);
    }
}
