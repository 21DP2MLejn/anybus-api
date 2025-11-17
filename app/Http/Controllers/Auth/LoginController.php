<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\LoginDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\LoginService;
use App\Traits\ApiResponse;

class LoginController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly LoginService $loginService
    ) {
    }

    /**
     * Authenticate a user and return a token.
     */
    public function __invoke(LoginRequest $request)
    {
        $dto = LoginDTO::fromArray($request->validated());
        $result = $this->loginService->login($dto);

        if (! $result) {
            return $this->errorResponse(
                'Invalid credentials.',
                401
            );
        }

        return $this->successResponse(
            [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
            'Login successful.'
        );
    }
}

