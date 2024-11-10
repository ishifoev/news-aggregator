<?php

namespace Tests\Unit;

use App\Contracts\UserRepositoryInterface;
use App\Events\UserRegistered;
use Tests\TestCase;
use App\Services\AuthService;
use App\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Mockery;
use Illuminate\Support\Facades\Event;

class AuthServiceTest extends TestCase
{
    protected AuthService $authService;
    protected UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->authService = new AuthService($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @throws ValidationException
     */
    public function test_login_returns_token_on_successful_authentication()
    {
        Log::shouldReceive('info')->once()->with('Login attempt', ['email' => 'john@example.com']);

        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'john@example.com', 'password' => 'password'])
            ->andReturn(true);

        $mockUser = Mockery::mock(User::class);
        $mockUser->shouldReceive('createToken')
            ->once()
            ->with('auth_token')
            ->andReturn((object) ['plainTextToken' => 'dummy_token']);

        $this->userRepository->shouldReceive('findByEmail')
            ->once()
            ->with('john@example.com')
            ->andReturn($mockUser);

        $credentials = [
            'email' => 'john@example.com',
            'password' => 'password',
        ];

        $result = $this->authService->login($credentials);

        $this->assertEquals('dummy_token', $result);
    }

    public function test_login_throws_validation_exception_on_failed_authentication()
    {
        Log::shouldReceive('info')->once()->with('Login attempt', ['email' => 'john@example.com']);
        Log::shouldReceive('warning')->once()->with('Login failed', ['email' => 'john@example.com']);

        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'john@example.com', 'password' => 'wrongpassword'])
            ->andReturn(false);

        $this->expectException(ValidationException::class);

        $credentials = [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ];

        $this->authService->login($credentials);
    }

    public function test_register_creates_user_and_returns_token()
    {
        Log::shouldReceive('info')->once()->with('Registration attempt', ['email' => 'john@example.com']);

        $mockUser = Mockery::mock(User::class);
        $mockUser->shouldReceive('createToken')
            ->once()
            ->with('auth_token')
            ->andReturn((object) ['plainTextToken' => 'dummy_token']);

        $this->userRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturn($mockUser);

        Event::fake();

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
        ];

        $result = $this->authService->register($data);

        $this->assertEquals('dummy_token', $result);
        Event::assertDispatched(UserRegistered::class, function ($event) use ($mockUser) {
            return $event->user === $mockUser;
        });
    }
}
