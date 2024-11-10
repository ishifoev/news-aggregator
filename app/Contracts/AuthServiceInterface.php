<?php

namespace App\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * @param array $data
     * @return string
     */
    public function register(array $data): string;

    public function login(array $credentials): string;

    public function logout(): void;

}
