<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ValidateResetTokenController;
use App\Http\Controllers\Job\JobActionController;
use App\Http\Controllers\Job\JobController;
use App\Http\Controllers\Job\JobFeedController;
use App\Http\Controllers\Job\SwipeJobController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\User\UserSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Authentication routes
Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class);
Route::post('/logout', LogoutController::class)->middleware('auth:sanctum');

// Email verification routes
Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
    ->middleware('auth:sanctum');
Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify');

// Password reset routes
Route::post('/forgot-password', ForgotPasswordController::class);
Route::post('/validate-reset-token', ValidateResetTokenController::class);
Route::post('/reset-password', ResetPasswordController::class);

// User profile routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/profile', [UserProfileController::class, 'show']);
    Route::post('/user/profile', [UserProfileController::class, 'store']);
    Route::put('/user/profile', [UserProfileController::class, 'update']);
    Route::delete('/user/profile', [UserProfileController::class, 'destroy']);
});

// User settings routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/settings', [UserSettingController::class, 'show']);
    Route::post('/user/settings', [UserSettingController::class, 'store']);
});

// Job routes
Route::middleware('auth:sanctum')->group(function () {
    // Job feed (worker only)
    Route::get('/jobs/feed', [JobFeedController::class, 'index']);

    // Job CRUD
    Route::get('/jobs', [JobController::class, 'index']);
    Route::post('/jobs', [JobController::class, 'store']);
    Route::get('/jobs/{job}', [JobController::class, 'show']);

    // Job actions
    Route::post('/jobs/{job}/accept', [JobActionController::class, 'accept']);
    Route::post('/jobs/{job}/investigate', [JobActionController::class, 'investigate']);
    Route::post('/jobs/{job}/prepare', [JobActionController::class, 'prepare']);
    Route::post('/jobs/{job}/start', [JobActionController::class, 'start']);
    Route::post('/jobs/{job}/complete', [JobActionController::class, 'complete']);
    Route::post('/jobs/{job}/cancel', [JobActionController::class, 'cancel']);

    // Job swipe
    Route::post('/jobs/{job}/swipe', [SwipeJobController::class, 'swipe']);
});
