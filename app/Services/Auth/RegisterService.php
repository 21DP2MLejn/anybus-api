<?php

namespace App\Services\Auth;

use App\Actions\Auth\CreateUserAction;
use App\Actions\Auth\GenerateTokenAction;
use App\Actions\Auth\SendVerificationEmailAction;
use App\DTO\Auth\RegisterDTO;
use App\Models\User;
use Laravel\Sanctum\NewAccessToken;

class RegisterService
{
    public function __construct(
        private readonly CreateUserAction $createUserAction,
        private readonly GenerateTokenAction $generateTokenAction,
        private readonly SendVerificationEmailAction $sendVerificationEmailAction
    ) {
    }

    /**
     * Register a new user.
     *
     * @param  RegisterDTO  $dto
     * @return array{user: User, token: string}
     */
    public function register(RegisterDTO $dto): array
    {
        $user = $this->createUserAction->execute($dto);
        $token = $this->generateTokenAction->execute($user);

        $this->sendVerificationEmailAction->execute($user);

        $token = $token->plainTextToken;
        
        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}

