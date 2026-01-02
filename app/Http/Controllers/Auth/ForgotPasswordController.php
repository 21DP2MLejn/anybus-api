<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\ForgotPasswordDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Services\Auth\ForgotPasswordService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ForgotPasswordService $forgotPasswordService
    ) {}

    /**
     * Send password reset link.
     */
    public function __invoke(ForgotPasswordRequest $request)
    {
        $dto = ForgotPasswordDTO::fromArray($request->validated());
        $status = $this->forgotPasswordService->sendResetLink($dto);

        // Always return success to prevent email enumeration
        $message = $status === Password::RESET_LINK_SENT
            ? 'Password reset link sent successfully.'
            : 'If the email exists, a password reset link has been sent.';

        return $this->successResponse(
            null,
            $message
        );
    }
}
