<?php

namespace App\Mail\Client;

use App\Mail\TemplatedEmail;
use App\Models\User;

/**
 * Copy for this email is editable from the admin dashboard
 * (Email Templates -> "client_fifth_review"); the shipped default is used until it is.
 */
class FifthReviewEmail extends TemplatedEmail
{
    public function __construct(User $user, string $browseUrl)
    {
        parent::__construct($user->name ?: 'there', $browseUrl);
    }

    protected function templateKey(): string
    {
        return 'client_fifth_review';
    }
}
