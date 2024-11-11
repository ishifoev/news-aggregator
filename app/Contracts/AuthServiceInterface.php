<?php

namespace App\Contracts;

interface AuthServiceInterface
{
    public function register(array $data): string;

    public function login(array $credentials): string;

    public function logout(): void;

    public function sendPasswordResetLink(string $email);
}
