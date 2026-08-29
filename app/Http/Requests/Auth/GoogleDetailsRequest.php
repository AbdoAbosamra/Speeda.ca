<?php

namespace App\Http\Requests\Auth;

/**
 * Step two of a Google signup, for either role.
 *
 * Extends the normal registration request rather than restating its rules:
 * the phone format, the unique checks, the approved city list and the trade
 * validation must stay identical no matter which door someone came in
 * through. Only the two fields Google already settled — the email address and
 * the password that does not exist — are dropped.
 *
 * The role arrives from the form rather than the session so that someone who
 * pressed the wrong button can still correct it here instead of starting the
 * whole round trip to Google again.
 */
class GoogleDetailsRequest extends RegisterRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // Google supplied the address and proved it, and a Google account has
        // no password to set.
        unset($rules['email'], $rules['password']);

        // Google hands us a display name and the form pre-fills it, so there
        // is no reason to accept a blank one from either role.
        $rules['name'][0] = 'required';

        // The parent only asks providers to accept the terms because the
        // client half of the registration form carries its own checkbox. This
        // form is the only place a Google signup sees them, so both roles do.
        $rules['terms'] = ['required', 'accepted'];

        return $rules;
    }
}
