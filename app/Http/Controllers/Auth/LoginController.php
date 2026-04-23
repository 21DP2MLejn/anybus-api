<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\LoginDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\LoginService;
use App\Traits\ApiResponse;
use OpenApi\Attributes as OA;

class LoginController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly LoginService $loginService
    ) {}

    #[OA\Post(
        path: '/login',
        summary: 'Authenticate a user and return a token',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'data', properties: [
                            new OA\Property(property: 'user', type: 'object'),
                            new OA\Property(property: 'token', type: 'string'),
                        ]),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Invalid credentials'),
        ]
    )]
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
