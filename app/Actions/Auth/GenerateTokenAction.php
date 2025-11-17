<?php

namespace App\Actions\Auth;

use App\Models\User;
use Laravel\Sanctum\NewAccessToken;

class GenerateTokenAction
{
    /**
     * Generate a Sanctum token for the user.
     *
     * @param  User  $user
     * @param  string  $deviceName
     * @return NewAccessToken
     */
    public function execute(User $user, string $deviceName = 'api-token'): NewAccessToken
    {
        return $user->createToken($deviceName);
    }
}

