<?php

namespace App\Services\Auth;

use App\Actions\Auth\ResetPasswordAction;
use App\DTO\Auth\ResetPasswordDTO;

class ResetPasswordService
{
    public function __construct(
        private readonly ResetPasswordAction $resetPasswordAction
    ) {
    }

    /**
     * Reset the user's password.
     *
     * @param  ResetPasswordDTO  $dto
     * @return string
     */
    public function resetPassword(ResetPasswordDTO $dto): string
    {
        return $this->resetPasswordAction->execute($dto);
    }
}

