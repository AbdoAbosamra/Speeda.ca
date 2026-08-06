<?php

namespace App\Mail\Provider;

use App\Mail\TemplatedEmail;
use App\Models\ServiceProvider;

/**
 * Motivational email sent to a provider when they receive their very first approved customer review.
 * Copy for this email is editable from the admin dashboard ("provider_first_review_received").
 */
class FirstReviewReceivedEmail extends TemplatedEmail
{
    public function __construct(ServiceProvider $provider, string $profileUrl)
    {
        $name = $provider->company_name ?: ($provider->user?->name ?: 'Service Provider');
        parent::__construct($name, $profileUrl);
    }

    protected function templateKey(): string
    {
        return 'provider_first_review_received';
    }
}
