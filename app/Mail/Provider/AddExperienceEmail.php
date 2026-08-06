<?php

namespace App\Mail\Provider;

use App\Mail\TemplatedEmail;
use App\Models\ServiceProvider;

/**
 * Copy for this email is editable from the admin dashboard
 * (Email Templates -> "experience"); the shipped default is used until it is.
 */
class AddExperienceEmail extends TemplatedEmail
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
        return 'experience';
    }

    protected function progress(): array
    {
        return ['label' => 'Onboarding Progress', 'text' => 'Step 3 of 6 complete (50%)', 'percent' => 50];
    }
}
