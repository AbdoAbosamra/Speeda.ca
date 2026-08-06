<?php

namespace App\Mail\Provider;

use App\Mail\TemplatedEmail;
use App\Models\ServiceProvider;

/**
 * Milestone motivational email sent to a provider when they reach 5 approved customer reviews.
 * Copy for this email is editable from the admin dashboard ("provider_fifth_review_received").
 */
class FifthReviewReceivedEmail extends TemplatedEmail
{
    public function __construct(ServiceProvider $provider, string $profileUrl)
    {
        $name = $provider->company_name ?: ($provider->user?->name ?: 'Service Provider');
        parent::__construct($name, $profileUrl);
    }

    protected function templateKey(): string
    {
        return 'provider_fifth_review_received';
    }
}
