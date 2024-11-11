<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UserPreferenceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_user_preferences_with_factory_data()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        UserPreference::factory()->create(['user_id' => $user['id']]);

        $response = $this->getJson('/api/v1/user/preferences');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['user_id', 'preferred_sources']);
    }

    public function test_authenticated_user_can_get_preferences()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        UserPreference::factory()->create([
            'user_id' => $user->id,
            'preferred_sources' => json_encode(['TechCrunch']),
            'preferred_categories' => json_encode(['Technology']),
            'preferred_authors' => json_encode(['John Doe']),
        ]);

        $response = $this->getJson('/api/v1/user/preferences');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['user_id', 'preferred_sources'])
            ->assertJson(['user_id' => $user->id]);
    }

    public function test_unauthenticated_user_cannot_access_preferences()
    {
        $response = $this->postJson('/api/v1/user/preferences', [
            'preferred_sources' => ['TechCrunch'],
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
