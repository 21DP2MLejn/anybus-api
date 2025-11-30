<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateResetTokenRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ValidateResetTokenController extends Controller
{
    use ApiResponse;

    /**
     * Validate the reset token and store validated data in session.
     */
    public function __invoke(ValidateResetTokenRequest $request)
    {
        $validated = $request->validated();

        // Find the user by email
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return $this->errorResponse(
                'Invalid or expired password reset token.',
                400
            );
        }

        // Validate the token using Laravel's built-in validation
        $status = Password::broker()->getRepository()->exists(
            $user,
            $validated['token']
        );

        if (!$status) {
            return $this->errorResponse(
                'Invalid or expired password reset token.',
                400
            );
        }

        // Generate a temporary session identifier
        $sessionId = 'reset_' . Str::random(40);

        // Store validated reset data in cache with the temporary ID
        Cache::put($sessionId, [
            'email' => $validated['email'],
            'token' => $validated['token'],
            'expires_at' => now()->addMinutes(30), // 30 minutes should be enough for user to reset
        ], 30); // 30 minutes

        return $this->successResponse([
            'session_id' => $sessionId,
        ], 'Token validated successfully.');
    }
}
