<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ResetPassword extends ResetPasswordNotification
{
    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     */
    protected function resetUrl($notifiable): string
    {
        $sessionId = Str::random(60);

        // Store session data in cache with 1 hour expiration
        Cache::put($sessionId, [
            'email' => $notifiable->getEmailForPasswordReset(),
            'token' => $this->token,
            'expires_at' => Carbon::now()->addHour(),
        ], 3600);

        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3005'));

        return $frontendUrl.'/reset-password/'.$sessionId;
    }
}
