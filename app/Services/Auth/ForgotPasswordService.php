<?php

namespace App\Services\Auth;

use App\Actions\Auth\SendPasswordResetAction;
use App\DTO\Auth\ForgotPasswordDTO;

class ForgotPasswordService
{
    public function __construct(
        private readonly SendPasswordResetAction $sendPasswordResetAction
    ) {
    }

    /**
     * Send password reset link to the user.
     *
     * @param  ForgotPasswordDTO  $dto
     * @return string
     */
    public function sendResetLink(ForgotPasswordDTO $dto): string
    {
        return $this->sendPasswordResetAction->execute($dto->email);
    }
}

