<?php

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Finds a user by their email address.
     *
     * @param  string  $email  The email address to search for.
     * @return User|null The user object if found, otherwise null.
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Creates a new user record in the database with the provided data.
     * Hashes the user's password before storing it for security.
     *
     * @param  array  $data  The user data to create a new user record.
     * @return User The newly created user object.
     */
    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }
}
