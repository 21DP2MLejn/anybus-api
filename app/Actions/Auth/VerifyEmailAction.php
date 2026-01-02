<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;

class VerifyEmailAction
{
    /**
     * Verify the user's email address.
     */
    public function execute(int $userId, string $hash): bool
    {
        $user = User::findOrFail($userId);

        if ($user->hasVerifiedEmail()) {
            return false;
        }

        if (! hash_equals((string) $user->getKey(), (string) $userId)) {
            return false;
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return false;
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));

            return true;
        }

        return false;
    }
}
