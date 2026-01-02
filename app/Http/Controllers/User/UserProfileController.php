<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserProfileRequest;
use App\Http\Requests\User\UpdateUserProfileRequest;
use App\Http\Resources\UserProfileResource;
use App\Services\User\UserProfileService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserProfileController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly UserProfileService $userProfileService
    ) {}

    /**
     * Display the user's profile.
     */
    public function show(): JsonResponse
    {
        $profile = $this->userProfileService->getUserProfile(auth()->id());

        if (! $profile) {
            return $this->errorResponse('Profile not found.', 404);
        }

        return $this->successResponse(
            new UserProfileResource($profile),
            'Profile retrieved successfully.'
        );
    }

    /**
     * Store a new user profile.
     */
    public function store(StoreUserProfileRequest $request): JsonResponse
    {
        $profile = $this->userProfileService->createUserProfile(
            auth()->id(),
            $request->validated()
        );

        return $this->successResponse(
            new UserProfileResource($profile),
            'Profile created successfully.',
            201
        );
    }

    /**
     * Update the user's profile.
     */
    public function update(UpdateUserProfileRequest $request): JsonResponse
    {
        $profile = $this->userProfileService->updateUserProfile(
            auth()->id(),
            $request->validated()
        );

        return $this->successResponse(
            new UserProfileResource($profile),
            'Profile updated successfully.'
        );
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(): JsonResponse
    {
        $this->userProfileService->deleteUserProfile(auth()->id());

        return $this->successResponse(
            null,
            'Profile deleted successfully.'
        );
    }
}
