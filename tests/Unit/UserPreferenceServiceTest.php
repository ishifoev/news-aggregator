<?php

namespace Tests\Unit;

use App\Contracts\UserPreferenceRepositoryInterface;
use App\Models\UserPreference;
use App\Services\UserPreferenceService;
use Mockery;
use Tests\TestCase;

class UserPreferenceServiceTest extends TestCase
{
    protected UserPreferenceRepositoryInterface $userPreferenceRepositoryMock;

    protected UserPreferenceService $userPreferenceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userPreferenceRepositoryMock = Mockery::mock(UserPreferenceRepositoryInterface::class);
        $this->userPreferenceService = new UserPreferenceService($this->userPreferenceRepositoryMock);
    }

    public function test_set_user_preferences_saves_and_returns_data()
    {
        $userId = 1;
        $preferences = [
            'preferred_sources' => ['TechCrunch'],
            'preferred_categories' => ['Technology'],
            'preferred_authors' => ['John Doe'],
        ];
        $userPreference = new UserPreference($preferences + ['user_id' => $userId]);

        $this->userPreferenceRepositoryMock
            ->shouldReceive('setUserPreferences')
            ->once()
            ->with($userId, $preferences)
            ->andReturn($userPreference);

        $result = $this->userPreferenceService->setUserPreferences($userId, $preferences);

        $this->assertInstanceOf(UserPreference::class, $result);
        $this->assertEquals($userId, $result->user_id);
    }

    public function test_get_user_preferences_returns_data()
    {
        $userId = 1;
        $userPreference = new UserPreference([
            'user_id' => $userId,
            'preferred_sources' => ['TechCrunch'],
            'preferred_categories' => ['Technology'],
            'preferred_authors' => ['John Doe'],
        ]);

        $this->userPreferenceRepositoryMock
            ->shouldReceive('getUserPreferences')
            ->once()
            ->with($userId)
            ->andReturn($userPreference);

        $result = $this->userPreferenceService->getUserPreferences($userId);

        $this->assertInstanceOf(UserPreference::class, $result);
        $this->assertEquals($userId, $result->user_id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
