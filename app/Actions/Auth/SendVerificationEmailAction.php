<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class SendVerificationEmailAction
{
    /**
     * Send email verification notification to the user.
     *
     * @param  User&MustVerifyEmail  $user
     * @return void
     */
    public function execute(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->sendEmailVerificationNotification();
    }
}

