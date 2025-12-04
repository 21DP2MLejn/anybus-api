<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ResetPassword extends ResetPasswordNotification
{
    /**
     * Get the reset URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
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

