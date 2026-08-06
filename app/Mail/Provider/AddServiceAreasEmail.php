<?php

namespace App\Mail\Provider;

use App\Mail\TemplatedEmail;
use App\Models\ServiceProvider;

/**
 * Copy for this email is editable from the admin dashboard
 * (Email Templates -> "service_areas"); the shipped default is used until it is.
 */
class AddServiceAreasEmail extends TemplatedEmail
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
        return 'service_areas';
    }

    protected function progress(): array
    {
        return ['label' => 'Onboarding Progress', 'text' => 'Step 5 of 6 complete (83%)', 'percent' => 83];
    }
}
