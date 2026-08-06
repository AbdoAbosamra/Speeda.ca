<?php

namespace App\Mail\Provider;

use App\Mail\TemplatedEmail;
use App\Models\ServiceProvider;

/**
 * Copy for this email is editable from the admin dashboard
 * (Email Templates -> "gallery"); the shipped default is used until it is.
 */
class ShowcaseWorkEmail extends TemplatedEmail
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
        return 'gallery';
    }

    protected function progress(): array
    {
        return ['label' => 'Onboarding Progress', 'text' => 'Step 4 of 6 complete (67%)', 'percent' => 67];
    }
}
