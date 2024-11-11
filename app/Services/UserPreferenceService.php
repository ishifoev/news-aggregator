<?php

namespace App\Services;

use App\Contracts\UserPreferenceRepositoryInterface;
use App\Contracts\UserPreferenceServiceInterface;
use App\Models\UserPreference;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserPreferenceService implements UserPreferenceServiceInterface
{
    protected UserPreferenceRepositoryInterface $userPreferenceRepository;

    public function __construct(UserPreferenceRepositoryInterface $userPreferenceRepository)
    {
        $this->userPreferenceRepository = $userPreferenceRepository;
    }

    public function setUserPreferences(int $userId, array $preferences): UserPreference
    {
        try {
            Log::info("Setting user preferences for user ID: {$userId}", $preferences);

            return Cache::remember("user_preferences_{$userId}", 300, function () use ($userId, $preferences) {
                return $this->userPreferenceRepository->setUserPreferences($userId, $preferences);
            });
        } catch (Exception $e) {
            Log::error("Failed to set user preferences for user ID: {$userId}", ['error' => $e->getMessage()]);
            throw new Exception('An error occurred while setting user preferences. Please try again later.');
        }
    }

    public function getUserPreferences(int $userId): ?UserPreference
    {
        try {
            Log::info("Fetching user preferences for user ID: {$userId}");

            return Cache::remember("user_preferences_{$userId}", 300, function () use ($userId) {
                return $this->userPreferenceRepository->getUserPreferences($userId);
            });
        } catch (Exception $e) {
            Log::error("Failed to fetch user preferences for user ID: {$userId}", ['error' => $e->getMessage()]);
            throw new Exception('An error occurred while retrieving user preferences. Please try again later.');
        }
    }
}
