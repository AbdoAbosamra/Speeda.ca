<?php

namespace App\Mail\Provider;

use App\Mail\TemplatedEmail;
use App\Models\ServiceProvider;

/**
 * Copy for this email is editable from the admin dashboard
 * (Email Templates -> "photo"); the shipped default is used until it is.
 */
class AddProfilePhotoEmail extends TemplatedEmail
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
        return 'photo';
    }

    protected function stats(): array
    {
        return [
            ['value' => '3x', 'label' => 'More Views'],
            ['value' => '2x', 'label' => 'More Leads'],
            ['value' => '30s', 'label' => 'To Complete'],
        ];
    }
    protected function progress(): array
    {
        return ['label' => 'Onboarding Progress', 'text' => 'Step 0 of 6 complete (0%)', 'percent' => 0];
    }
}
