<?php

namespace App\Mail\Provider;

use App\Mail\TemplatedEmail;
use App\Models\ServiceProvider;

/**
 * Copy for this email is editable from the admin dashboard
 * (Email Templates -> "services"); the shipped default is used until it is.
 */
class AddServicesEmail extends TemplatedEmail
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
        return 'services';
    }

    protected function progress(): array
    {
        return ['label' => 'Onboarding Progress', 'text' => 'Step 1 of 6 complete (17%)', 'percent' => 17];
    }
}
