<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class SendVerificationEmailAction
{
    /**
     * Send email verification notification to the user.
     *
     * @param  User&MustVerifyEmail  $user
     */
    public function execute(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->sendEmailVerificationNotification();
    }
}
