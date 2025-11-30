<?php

namespace App\Http\Controllers\Auth;

use App\DTO\Auth\ResetPasswordDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\Auth\ResetPasswordService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ResetPasswordService $resetPasswordService
    ) {
    }

    /**
     * Reset the user's password.
     */
    public function __invoke(ResetPasswordRequest $request)
    {
        $validated = $request->validated();
        $data = $request->all();
        $data['password_confirmation'] = $request->input('password_confirmation');

        $dto = ResetPasswordDTO::fromArray($data);
        $status = $this->resetPasswordService->resetPassword($dto);

        if ($status !== Password::PASSWORD_RESET) {
            return $this->errorResponse(
                'Unable to reset password. Please check your token and try again.',
                400
            );
        }

        return $this->successResponse(
            null,
            'Password reset successfully.'
        );
    }
}

