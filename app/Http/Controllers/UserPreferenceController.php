<?php

namespace App\Http\Controllers;

use App\Contracts\UserPreferenceServiceInterface;
use App\Http\Requests\UserPreferenceRequest;
use Illuminate\Http\JsonResponse;

class UserPreferenceController extends Controller
{
    protected UserPreferenceServiceInterface $userPreferenceService;

    public function __construct(UserPreferenceServiceInterface $userPreferenceService)
    {
        $this->userPreferenceService = $userPreferenceService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/preferences",
     *     tags={"User Preferences"},
     *     summary="Set user preferences",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="preferred_sources", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="preferred_categories", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="preferred_authors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="User preferences saved")
     * )
     */
    public function setPreferences(UserPreferenceRequest $request): JsonResponse
    {
        $preferences = $request->validated();
        $userId = auth()->id();
        $userPreferences = $this->userPreferenceService->setUserPreferences($userId, $preferences);

        return response()->json($userPreferences);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/preferences",
     *     tags={"User Preferences"},
     *     summary="Get user preferences",
     *     @OA\Response(response=200, description="User preferences retrieved")
     * )
     */
    public function getPreferences(): JsonResponse
    {
        $userId = auth()->id();
        $userPreferences = $this->userPreferenceService->getUserPreferences($userId);

        return response()->json($userPreferences);
    }
}
