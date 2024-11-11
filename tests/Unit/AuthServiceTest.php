<?php

namespace Tests\Unit;

use App\Contracts\UserRepositoryInterface;
use App\Events\UserRegistered;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

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

        Log::shouldReceive('info')->once()->with('User logged in successfully', ['email' => 'john@example.com']);
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

    public function test_logout_logs_out_user_and_deletes_tokens()
    {
        $mockUser = Mockery::mock();
        $mockUser->shouldReceive('tokens->delete')->once();
        $mockUser->email = 'user@example.com';
        $mockUser->id = 1;

        // Simulate authenticated user
        Auth::shouldReceive('user')->andReturn($mockUser);

        Log::shouldReceive('info')->twice();

        $this->authService->logout();

        $this->assertTrue(true);
    }

    public function test_logout_logs_warning_when_no_authenticated_user()
    {
        Auth::shouldReceive('user')->andReturn(null);

        Log::shouldReceive('warning')->once()->with('Logout attempt failed: no authenticated user');

        $this->authService->logout();

        $this->assertTrue(true);
    }

    public function test_send_password_reset_link_logs_and_sends_email()
    {
        Log::shouldReceive('info')->once()->with('Password reset link request initiated', ['email' => 'john@example.com']);
        Password::shouldReceive('sendResetLink')->once()->with(['email' => 'john@example.com'])->andReturn(Password::RESET_LINK_SENT);

        Log::shouldReceive('info')->once()->with('Password reset link sent successfully', ['email' => 'john@example.com']);
        $this->authService->sendPasswordResetLink('john@example.com');

        $this->assertTrue(true);
    }
}
