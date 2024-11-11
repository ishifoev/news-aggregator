<?php

namespace Tests\Unit;

use App\Contracts\ArticleRepositoryInterface;
use App\Models\Article;
use App\Services\ArticleService;
use Mockery;
use Tests\TestCase;

class ArticleServiceTest extends TestCase
{
    protected ArticleService $articleService;

    protected $articleRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->articleRepositoryMock = Mockery::mock(ArticleRepositoryInterface::class);
        $this->articleService = new ArticleService($this->articleRepositoryMock);
    }

    public function test_get_articles_returns_paginated_data()
    {
        // Arrange: Create a mock paginated response
        $articles = Article::factory()->count(5)->make();
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator($articles, 5, 10);
        $this->articleRepositoryMock->shouldReceive('getArticles')
            ->once()
            ->andReturn($paginator);

        // Act: Call the service method
        $result = $this->articleService->getArticles([]);

        // Assert: Verify that the result is a paginated response
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
    }

    public function test_get_article_by_id_returns_correct_article()
    {
        // Arrange: Create a mock article
        $article = Article::factory()->make(['id' => 1, 'title' => 'Test Article']);
        $this->articleRepositoryMock->shouldReceive('findArticleById')
            ->with(1)
            ->once()
            ->andReturn($article);

        // Act: Call the service method
        $result = $this->articleService->getArticleById(1);

        // Assert: Verify the article is returned correctly
        $this->assertEquals('Test Article', $result->title);
    }

    public function test_get_article_by_id_throws_exception_for_nonexistent_id()
    {
        // Arrange: Configure the mock to throw a ModelNotFoundException
        $this->articleRepositoryMock->shouldReceive('findArticleById')
            ->with(999)
            ->once()
            ->andThrow(new \Illuminate\Database\Eloquent\ModelNotFoundException);

        // Assert: Expect a ModelNotFoundException to be thrown
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Act: Call the service method with a non-existent ID
        $this->articleService->getArticleById(999);
    }

    public function test_articles_endpoint_rate_limiting()
    {
        for ($i = 0; $i < 31; $i++) {
            $response = $this->getJson('/api/v1/articles');
        }

        $response->assertStatus(429)
            ->assertJson([
                'message' => 'Too many requests. Please wait before trying again.',
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
