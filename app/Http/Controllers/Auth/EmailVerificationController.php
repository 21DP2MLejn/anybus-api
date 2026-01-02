<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\EmailVerificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly EmailVerificationService $emailVerificationService
    ) {}

    /**
     * Send email verification notification.
     */
    public function send(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->errorResponse(
                'Email already verified.',
                409
            );
        }

        $this->emailVerificationService->sendVerificationEmail($request->user());

        return $this->successResponse(
            null,
            'Verification email sent successfully.'
        );
    }

    /**
     * Verify the user's email address.
     */
    public function verify(Request $request, int $id, string $hash)
    {
        $verified = $this->emailVerificationService->verifyEmail($id, $hash);

        if (! $verified) {
            return $this->errorResponse(
                'Invalid verification link or email already verified.',
                400
            );
        }

        return $this->successResponse(
            null,
            'Email verified successfully.'
        );
    }
}
