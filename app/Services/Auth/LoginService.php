<?php

namespace App\Services\Auth;

use App\Actions\Auth\GenerateTokenAction;
use App\DTO\Auth\LoginDTO;
use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class LoginService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly GenerateTokenAction $generateTokenAction
    ) {
    }

    /**
     * Authenticate a user and generate a token.
     *
     * @param  LoginDTO  $dto
     * @return array{user: User, token: string}|null
     */
    public function login(LoginDTO $dto): ?array
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            return null;
        }

        $token = $this->generateTokenAction->execute($user);

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
    }
}

