<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        // Manually validate current password to control error key. A
        // Google-only account has none yet, so this is where it sets one.
        if (! $request->user()->isGoogleOnly()
            && ! Hash::check((string) $request->current_password, (string) $request->user()->password)) {
            return back()->withErrors([
                'current_password' => [__('auth.password_incorrect')],
            ], 'updatePassword');
        }

        $validated = $request->validateWithBag('updatePassword', [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
