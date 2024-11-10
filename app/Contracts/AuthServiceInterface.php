<?php

namespace App\Contracts;

interface AuthServiceInterface
{
    /**
     * @param array $data
     * @return mixed
     */
    public function register(array $data): mixed;

}
