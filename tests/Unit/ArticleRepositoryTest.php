<?php

namespace Tests\Unit;

use App\Repositories\ArticleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_articles_with_sources()
    {
        $repository = new ArticleRepository;
        $articles = [
            [
                'title' => 'Sample Article',
                'content' => 'Sample Content',
                'author' => 'Author Name',
                'published_at' => now()->toDateTimeString(),
                'source' => 'Sample Source',
                'category' => 'Tech',
                'url' => 'http://example.com/sample-article',
            ],
        ];

        $repository->saveArticlesWithSources($articles);

        $this->assertDatabaseHas('articles', ['title' => 'Sample Article']);
        $this->assertDatabaseHas('sources', ['name' => 'Sample Source']);
    }
}
