<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['access_token', 'token_type']);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_user_registration_fails_with_invalid_data()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'pass',
            'password_confirmation' => 'notmatching',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_user_can_login()
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'john@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['access_token', 'token_type']);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'invalid@example.com',
            'password' => 'invalidpassword',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(['message' => 'Invalid login details']);
    }

    public function test_user_can_logout_successfully()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'Successfully logged out']);
    }

    public function test_guest_cannot_logout()
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_user_can_request_password_reset_link()
    {
        Notification::fake();

        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/password-reset', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Password reset link sent']);
    }

    public function test_password_reset_link_fails_for_nonexistent_email()
    {
        $response = $this->postJson('/api/v1/password-reset', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'We could not find a user with that email address.']);
    }

    public function test_rate_limit_exceeded_on_login()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Simulate reaching the rate limit
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);
        }

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(Response::HTTP_TOO_MANY_REQUESTS)
            ->assertJson([
                'message' => 'Too many requests. Please wait before trying again.',
            ])
            ->assertJsonStructure(['retry_after']);
    }

    public function test_rate_limit_exceeded_on_register()
    {
        for ($i = 0; $i <= 20; $i++) {
            $this->postJson('/api/v1/register', [
                'name' => 'John Doe',
                'email' => 'test'.$i.'@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);
        }

        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'test6@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(Response::HTTP_TOO_MANY_REQUESTS)
            ->assertJson([
                'message' => 'Too many requests. Please wait before trying again.',
            ])
            ->assertJsonStructure(['retry_after']);
    }
}
