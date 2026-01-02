<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserSettingRequest;
use App\Http\Resources\UserSettingResource;
use App\Services\User\UserSettingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserSettingController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly UserSettingService $userSettingService
    ) {}

    /**
     * Display the user's settings.
     */
    public function show(): JsonResponse
    {
        $settings = $this->userSettingService->getUserSettings(auth()->id());

        if (! $settings) {
            return $this->errorResponse('Settings not found.', 404);
        }

        return $this->successResponse(
            new UserSettingResource($settings),
            'Settings retrieved successfully.'
        );
    }

    /**
     * Store or update user settings (using updateOrCreate).
     */
    public function store(StoreUserSettingRequest $request): JsonResponse
    {
        $settings = $this->userSettingService->storeUserSettings(
            auth()->id(),
            $request->validated()
        );

        return $this->successResponse(
            new UserSettingResource($settings),
            'Settings saved successfully.'
        );
    }
}
