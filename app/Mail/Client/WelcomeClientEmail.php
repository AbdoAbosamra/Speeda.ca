<?php

namespace App\Mail\Client;

use App\Mail\TemplatedEmail;
use App\Models\User;

/**
 * Welcome email sent to regular client users upon registration.
 * Copy for this email is editable from the admin dashboard ("client_welcome").
 */
class WelcomeClientEmail extends TemplatedEmail
{
    public function __construct(User $user, string $browseUrl)
    {
        parent::__construct($user->name ?: 'there', $browseUrl);
    }

    protected function templateKey(): string
    {
        return 'client_welcome';
    }
}
