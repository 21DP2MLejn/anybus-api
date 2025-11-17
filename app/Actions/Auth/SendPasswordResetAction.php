<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Password;

class SendPasswordResetAction
{
    /**
     * Send password reset notification to the user.
     *
     * @param  string  $email
     * @return string
     */
    public function execute(string $email): string
    {
        $status = Password::sendResetLink(['email' => $email]);

        return $status;
    }
}

