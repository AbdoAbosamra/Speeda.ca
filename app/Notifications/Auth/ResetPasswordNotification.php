<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends ResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        $supportEmail = config('auth.password_reset.support_email', 'support@speeda.ca');
        $fromAddress = config('mail.from.address', $supportEmail);
        $fromName = config('mail.from.name', config('app.name', 'Speeda'));
        $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->from($fromAddress, $fromName)
            ->replyTo($supportEmail, $fromName)
            ->subject(Lang::get('auth.reset_password_email_subject'))
            ->greeting(Lang::get('auth.reset_password_email_greeting'))
            ->line(Lang::get('auth.reset_password_email_intro'))
            ->action(Lang::get('auth.reset_password_email_action'), $url)
            ->line(Lang::get('auth.reset_password_email_expire', ['count' => $expireMinutes]))
            ->line(Lang::get('auth.reset_password_email_ignore', ['email' => $supportEmail]))
            ->salutation(Lang::get('auth.reset_password_email_salutation'));
    }
}
