<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

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
        // This should point to your frontend reset password page
        // The token will be included in the URL
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3005'));
        
        return $frontendUrl.'/reset-password/'.$this->token.'?email='.urlencode($notifiable->getEmailForPasswordReset());
    }
}

