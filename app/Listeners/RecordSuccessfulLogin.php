<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\UserEngagementEmailService;
use Illuminate\Auth\Events\Login;

/**
 * Records login metadata (count, timestamp, IP) whenever a user authenticates.
 * Fired for every successful Auth::attempt / Auth::login, so it covers the
 * email and mobile-phone login paths alike.
 */
class RecordSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (!$user instanceof User) {
            return;
        }

        $user->forceFill([
            'login_count' => (int) ($user->login_count ?? 0) + 1,
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
            'last_seen_at' => now(),
        ])->saveQuietly();

        app(UserEngagementEmailService::class)->sendClientWelcomeEmail($user);
    }
}
