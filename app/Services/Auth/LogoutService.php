<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;

class LogoutService
{
    /**
     * Logout the authenticated user by revoking all tokens.
     */
    public function logout(Request $request): void
    {
        $request->user()->tokens()->delete();
    }

    /**
     * Logout the authenticated user by revoking the current token.
     */
    public function logoutCurrent(Request $request): void
    {
        $request->user()->currentAccessToken()->delete();
    }
}
