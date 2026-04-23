<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserProfileRequest;
use App\Http\Requests\User\UpdateUserProfileRequest;
use App\Http\Resources\UserProfileResource;
use App\Services\User\UserProfileService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class UserProfileController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly UserProfileService $userProfileService
    ) {}

    #[OA\Get(
        path: '/user/profile',
        summary: "Display the user's profile",
        security: [['sanctum' => []]],
        tags: ['User Profile'],
        responses: [
            new OA\Response(response: 200, description: 'Profile retrieved successfully'),
            new OA\Response(response: 404, description: 'Profile not found'),
        ]
    )]
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

    #[OA\Post(
        path: '/user/profile',
        summary: 'Store a new user profile',
        security: [['sanctum' => []]],
        tags: ['User Profile'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'John'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
                    new OA\Property(property: 'phone', type: 'string', example: '+3725555555'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Profile created successfully'),
        ]
    )]
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

    #[OA\Put(
        path: '/user/profile',
        summary: "Update the user's profile",
        security: [['sanctum' => []]],
        tags: ['User Profile'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'John'),
                    new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Profile updated successfully'),
        ]
    )]
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

    #[OA\Delete(
        path: '/user/profile',
        summary: "Delete the user's profile",
        security: [['sanctum' => []]],
        tags: ['User Profile'],
        responses: [
            new OA\Response(response: 200, description: 'Profile deleted successfully'),
        ]
    )]
    public function destroy(): JsonResponse
    {
        $this->userProfileService->deleteUserProfile(auth()->id());

        return $this->successResponse(
            null,
            'Profile deleted successfully.'
        );
    }
}
