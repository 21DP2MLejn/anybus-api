<?php

namespace App\Actions\Auth;

use App\DTO\Auth\ResetPasswordDTO;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ResetPasswordAction
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * Reset the user's password.
     */
    public function execute(ResetPasswordDTO $dto): string
    {
        $status = Password::reset(
            [
                'email' => $dto->email,
                'token' => $dto->token,
                'password' => $dto->password,
                'password_confirmation' => $dto->passwordConfirmation,
            ],
            function ($user, $password) {
                $this->userRepository->update($user, [
                    'password' => Hash::make($password),
                ]);
            }
        );

        return $status;
    }
}
