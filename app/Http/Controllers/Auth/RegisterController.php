<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\RegisterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\RegisterService;
use App\Traits\ApiResponse;

class RegisterController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly RegisterService $registerService
    ) {
    }

    /**
     * Register a new user.
     */
    public function __invoke(RegisterRequest $request)
    {
        $dto = RegisterDTO::fromArray($request->validated());
        $result = $this->registerService->register($dto);

        return $this->successResponse(
            [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
            'User registered successfully. Please verify your email.',
            201
        );
    }
}

