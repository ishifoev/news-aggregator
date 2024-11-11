<?php
namespace App\Services;

use App\Contracts\AuthServiceInterface;
use App\Contracts\UserRepositoryInterface;
use App\Events\UserRegistered;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthService implements AuthServiceInterface {
    /**
     * @param array $data
     * @return mixed
     */
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login(array $credentials): string {
        Log::info('Login attempt', ['email' => $credentials['email']]);

        if (!Auth::attempt($credentials)) {
            Log::warning('Login failed', ['email' => $credentials['email']]);
            throw ValidationException::withMessages(['message' => 'Invalid login details']);
        }

        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user) {
            throw new \Exception('User not found after successful login attempt');
        }

        Log::info('User logged in successfully', ['email' => $credentials['email']]);

        return $user->createToken('auth_token')->plainTextToken;
    }

    /**
     * @throws \Exception
     */
    public function register(array $data): string {
        Log::info('Registration attempt', ['email' => $data['email']]);
        try {
            $user = $this->userRepository->create($data);

            event(new UserRegistered($user));

            return $user->createToken('auth_token')->plainTextToken;
        }  catch (\Exception $e) {
            Log::error('Registration failed', ['email' => $data['email'], 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * @return void
     */
    public function logout(): void
    {
        $user = auth()->user();

        if ($user) {
            Log::info('Logout attempt', ['user_id' => $user->id, 'email' => $user->email]);
            $user->tokens()->delete();
            Log::info('Logout successful', ['user_id' => $user->id]);
        } else {
            Log::warning('Logout attempt failed: no authenticated user');
        }
    }

    public function sendPasswordResetLink(string $email): void {
        Log::info('Password reset link requested', ['email' => $email]);
        Password::sendResetLink(['email' => $email]);
    }

}
