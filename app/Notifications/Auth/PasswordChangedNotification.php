<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;

/**
 * Tells the account owner their password was just changed from the account
 * page, so a change they did not make is noticed and can be undone with a
 * reset link. Queued like every other email, so a mail outage never turns a
 * saved password into an error page.
 */
class PasswordChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Carbon $changedAt)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $supportEmail = config('auth.password_reset.support_email', 'support@speeda.ca');
        $fromAddress = config('mail.from.address', $supportEmail);
        $fromName = config('mail.from.name', config('app.name', 'Speeda'));

        return (new MailMessage)
            ->from($fromAddress, $fromName)
            ->replyTo($supportEmail, $fromName)
            ->subject(Lang::get('auth.password_changed_email_subject'))
            ->greeting(Lang::get('auth.reset_password_email_greeting'))
            ->line(Lang::get('auth.password_changed_email_intro', [
                'email' => $notifiable->email,
                'time' => $this->changedAt->timezone(config('app.timezone'))->format('Y-m-d H:i'),
            ]))
            ->line(Lang::get('auth.password_changed_email_if_you'))
            ->line(Lang::get('auth.password_changed_email_if_not'))
            ->action(Lang::get('auth.reset_password_email_action'), route('password.request'))
            ->line(Lang::get('auth.password_changed_email_contact', ['email' => $supportEmail]))
            ->salutation(Lang::get('auth.reset_password_email_salutation'));
    }
}
