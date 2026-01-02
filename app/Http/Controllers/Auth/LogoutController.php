<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\LogoutService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly LogoutService $logoutService
    ) {}

    /**
     * Logout the authenticated user.
     */
    public function __invoke(Request $request)
    {
        $this->logoutService->logoutCurrent($request);

        return $this->successResponse(
            null,
            'Logged out successfully.'
        );
    }
}
