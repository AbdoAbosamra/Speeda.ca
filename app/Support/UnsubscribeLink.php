<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\URL;

/**
 * Builds the signed opt-out link that every non-essential email carries.
 *
 * Centralised so the footer link, the List-Unsubscribe header and the admin
 * broadcast sender can never drift apart — and so the signature (which is what
 * proves the link belongs to that one user, since no login is required) is
 * generated in exactly one place.
 */
final class UnsubscribeLink
{
    /**
     * Signed, per-recipient, login-free opt-out URL.
     *
     * Deliberately never expires: an unsubscribe link that has gone stale is
     * indistinguishable from an unsubscribe link that does not work, and that
     * is what gets mail marked as spam.
     */
    public static function for(User $user): string
    {
        return URL::signedRoute('email.unsubscribe', ['user' => $user->id]);
    }

    /**
     * Null-safe variant, for senders whose recipient may not be a User record
     * (a provider row whose account was deleted, for instance).
     */
    public static function forNullable(?User $user): ?string
    {
        return $user?->exists ? self::for($user) : null;
    }
}
