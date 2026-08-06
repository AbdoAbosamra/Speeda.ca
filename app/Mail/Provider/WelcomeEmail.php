<?php

namespace App\Mail\Provider;

use App\Mail\TemplatedEmail;
use App\Models\ServiceProvider;

/**
 * Copy for this email is editable from the admin dashboard
 * (Email Templates -> "welcome"); the shipped default is used until it is.
 */
class WelcomeEmail extends TemplatedEmail
{
    public function __construct(ServiceProvider $provider, string $dashboardUrl)
    {
        parent::__construct(
            $provider->company_name ?: ($provider->user?->name ?: 'Provider'),
            $dashboardUrl,
        );
    }

    protected function templateKey(): string
    {
        return 'welcome';
    }

    protected function progress(): array
    {
        return ['label' => 'Onboarding Progress', 'text' => 'Step 0 of 6 complete (0%)', 'percent' => 0];
    }
}
