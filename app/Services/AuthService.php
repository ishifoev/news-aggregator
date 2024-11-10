<?php
namespace App\Services;

use App\Contracts\AuthServiceInterface;
use App\Events\UserRegistered;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService implements AuthServiceInterface {
    /**
     * @param array $data
     * @return mixed
     */
    public function register(array $data): mixed {
        Log::info('Registration attempt', ['email' => $data['email']]);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new UserRegistered($user));

        return $user->createToken('auth_token')->plainTextToken;
    }
}
