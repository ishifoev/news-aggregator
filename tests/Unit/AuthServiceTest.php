<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AuthService;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Mockery;

class AuthServiceTest extends TestCase {
    protected function tearDown(): void
    {
        Mockery::close(); // Ensure Mockery is closed after tests
        parent::tearDown();
    }

    public function test_register_creates_user_and_returns_token()
    {
        Log::shouldReceive('info')->once(); // Mock logging

        $mockUser = Mockery::mock('overload:' . User::class);
        $mockUser->shouldReceive('create')
            ->once()
            ->andReturnSelf(); // Simulate User::create() behavior

        $mockUser->shouldReceive('createToken')
            ->once()
            ->with('auth_token')
            ->andReturn((object) ['plainTextToken' => 'dummy_token']);

        $authService = new AuthService();
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
        ];

        $result = $authService->register($data);

        $this->assertEquals('dummy_token', $result);
    }
}
