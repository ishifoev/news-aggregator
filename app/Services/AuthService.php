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

    /**
     * Authenticates a user and returns an access token upon successful login.
     *
     * @param array $credentials User login credentials.
     * @return string The generated access token.
     * @throws ValidationException If authentication fails.
     */
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
     * Registers a new user and generates an access token.
     *
     * @param array $data User registration data.
     * @return string The generated access token.
     * @throws \Exception If user registration fails.
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
     * Logs out the currently authenticated user and deletes all tokens.
     *
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

    /**
     * Sends a password reset link to the provided email address.
     *
     * @param string $email The email address for the password reset link.
     * @return void
     * @throws \Exception If sending the password reset link fails.
     */
    public function sendPasswordResetLink(string $email): void {
        Log::info('Password reset link request initiated', ['email' => $email]);
        try {
            Password::sendResetLink(['email' => $email]);
            Log::info('Password reset link sent successfully', ['email' => $email]);
        } catch (\Exception $e) {
            Log::error('Password reset failed', ['email' => $email, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

}
