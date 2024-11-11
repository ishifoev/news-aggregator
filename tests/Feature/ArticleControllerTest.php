<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_articles_with_pagination()
    {
        Source::factory()->count(2)->create();
        Article::factory()->count(15)->create();

        $response = $this->getJson('/api/v1/articles');

        // Assert: Verify the response status and structure
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data', 'links']);
    }

    public function test_end_to_end_fetch_articles_with_filters()
    {
        $source = Source::factory()->create(['name' => 'TechCrunch']);
        Article::factory()->count(10)->create(['source_id' => $source->id, 'category' => 'Technology']);

        // Act: Send a GET request with filters
        $response = $this->getJson('/api/v1/articles?category=Technology&source=TechCrunch');

        // Assert: Verify response structure and status
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'content', 'source_id'],
                ],
                'links',
            ]);
    }

    public function test_can_fetch_single_article()
    {
        $source = Source::factory()->create();
        $article = Article::factory()->create(['source_id' => $source->id]);

        $response = $this->getJson("/api/v1/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'title', 'content', 'source_id'])
            ->assertJson(['id' => $article->id]);
    }

    public function test_fetching_nonexistent_article_returns_404()
    {
        $response = $this->getJson('/api/v1/articles/999');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_no_articles_found_returns_empty_response()
    {
        // Act: Send a GET request to fetch articles when no articles exist
        $response = $this->getJson('/api/v1/articles');

        // Assert: Verify the response is successful and returns no data
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [],
            ]);
    }

    public function test_fetching_articles_with_invalid_filters()
    {
        Source::factory()->create();
        Article::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/articles?date=invalid-date-format');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['date']);
    }
}
