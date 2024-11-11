<?php

namespace App\Contracts;

use App\Models\UserPreference;

interface UserPreferenceServiceInterface
{
    public function setUserPreferences(int $userId, array $preferences): UserPreference;

    public function getUserPreferences(int $userId): ?UserPreference;
}
