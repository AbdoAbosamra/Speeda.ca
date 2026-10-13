<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request)
    {
        // If user is a service provider, redirect to their service provider profile
        if ($request->user()->role === 'service_provider' && $request->user()->serviceProvider) {
            return redirect()->route('service-providers.show', $request->user()->serviceProvider->id);
        }

        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return redirect()->route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Admin accounts are removed from the dashboard, where the "never
        // delete the last admin" guard lives — not from this self-service page.
        if ($user->isAdmin()) {
            return back()->withErrors(['password' => [__('profile.admin_delete_blocked')]], 'userDeletion');
        }

        // A Google-only account has no password to type, so it confirms by
        // typing its own email address instead.
        if ($user->isGoogleOnly()) {
            if (mb_strtolower(trim((string) $request->input('confirm_email'))) !== mb_strtolower($user->email)) {
                return back()->withErrors(['confirm_email' => [__('profile.delete_confirm_email_mismatch')]], 'userDeletion');
            }
        } elseif (! Hash::check((string) $request->password, $user->password)) {
            return back()->withErrors([
                'password' => [__('auth.password_incorrect')],
            ], 'userDeletion');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
