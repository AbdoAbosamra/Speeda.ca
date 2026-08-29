<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Public, login-free opt-out reached from the footer of any Speeda email.
 *
 * Reached only through a signed URL embedded in the email, so the link cannot
 * be edited to unsubscribe another account. No login is required — people
 * expect unsubscribing to work straight from the inbox, and forcing a sign-in
 * is the classic way an opt-out link quietly stops working.
 *
 * This stops every non-essential email: the provider onboarding journey,
 * welcome mail, review notifications and admin broadcasts. Transactional mail
 * (password resets, email verification) keeps flowing, because the account
 * depends on it — and those emails carry no unsubscribe link for the same
 * reason.
 *
 * `broadcast_opt_out_at` is written alongside the global flag so the older
 * broadcast-only checks scattered through the sending code keep agreeing with
 * the user's decision.
 */
class EmailUnsubscribeController extends Controller
{
    public function show(User $user)
    {
        return view('email.unsubscribe', [
            'user' => $user,
            'alreadyOptedOut' => $user->hasOptedOutOfEmail(),
        ]);
    }

    /**
     * Also serves Gmail/Outlook one-click unsubscribe, which POSTs to the same
     * signed URL without a CSRF token (see the CSRF exception in
     * bootstrap/app.php).
     */
    public function store(Request $request, User $user)
    {
        if (!$user->hasOptedOutOfEmail()) {
            $user->forceFill([
                'email_opt_out_at' => now(),
                'broadcast_opt_out_at' => $user->broadcast_opt_out_at ?? now(),
            ])->save();

            Log::info('[Email] User unsubscribed from all non-essential email', [
                'user_id' => $user->id,
            ]);
        }

        // One-click clients expect a bare 200, not a rendered page.
        if ($request->expectsJson() || $request->header('List-Unsubscribe') !== null) {
            return response()->noContent();
        }

        return view('email.unsubscribe', [
            'user' => $user,
            'alreadyOptedOut' => true,
            'justUnsubscribed' => true,
        ]);
    }

    /**
     * Undo, for the "I clicked this by mistake" case.
     */
    public function resubscribe(User $user)
    {
        $user->forceFill([
            'email_opt_out_at' => null,
            'broadcast_opt_out_at' => null,
        ])->save();

        Log::info('[Email] User resubscribed', ['user_id' => $user->id]);

        return view('email.unsubscribe', [
            'user' => $user,
            'alreadyOptedOut' => false,
            'justResubscribed' => true,
        ]);
    }
}
