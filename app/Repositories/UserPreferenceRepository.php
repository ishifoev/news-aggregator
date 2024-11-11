<?php

namespace App\Repositories;

use App\Contracts\UserPreferenceRepositoryInterface;
use App\Models\UserPreference;
use Illuminate\Support\Facades\DB;

class UserPreferenceRepository implements UserPreferenceRepositoryInterface
{
    public function setUserPreferences(int $userId, array $preferences): UserPreference
    {
        return DB::transaction(function () use ($userId, $preferences) {
            return UserPreference::updateOrCreate(
                ['user_id' => $userId],
                [
                    'preferred_sources' => json_encode($preferences['preferred_sources']),
                    'preferred_categories' => json_encode($preferences['preferred_categories']),
                    'preferred_authors' => json_encode($preferences['preferred_authors']),
                ]
            );
        });
    }

    public function getUserPreferences(int $userId): ?UserPreference
    {
        return UserPreference::select('user_id', 'preferred_sources', 'preferred_categories', 'preferred_authors')
            ->where('user_id', $userId)
            ->first();
    }
}
