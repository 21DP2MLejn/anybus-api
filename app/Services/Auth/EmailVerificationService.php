<?php

namespace App\Services\Auth;

use App\Actions\Auth\SendVerificationEmailAction;
use App\Actions\Auth\VerifyEmailAction;
use App\Models\User;

class EmailVerificationService
{
    public function __construct(
        private readonly SendVerificationEmailAction $sendVerificationEmailAction,
        private readonly VerifyEmailAction $verifyEmailAction
    ) {
    }

    /**
     * Send email verification notification.
     *
     * @param  User  $user
     * @return void
     */
    public function sendVerificationEmail(User $user): void
    {
        $this->sendVerificationEmailAction->execute($user);
    }

    /**
     * Verify the user's email address.
     *
     * @param  int  $userId
     * @param  string  $hash
     * @return bool
     */
    public function verifyEmail(int $userId, string $hash): bool
    {
        return $this->verifyEmailAction->execute($userId, $hash);
    }
}

