<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Public, login-free opt-out from admin broadcast emails.
 *
 * Reached only through a signed URL embedded in the email, so the link cannot
 * be edited to unsubscribe another account. No login is required — CASL
 * expects unsubscribing to work straight from the inbox, and forcing a sign-in
 * is the classic way an "unsubscribe" quietly fails.
 *
 * This opts out of broadcasts only. Transactional mail (password resets,
 * review notifications) keeps flowing, because the account depends on it.
 */
class BroadcastUnsubscribeController extends Controller
{
    public function show(User $user)
    {
        return view('broadcast.unsubscribe', [
            'user' => $user,
            'alreadyOptedOut' => $user->broadcast_opt_out_at !== null,
        ]);
    }

    /**
     * Also serves Gmail/Outlook one-click unsubscribe, which POSTs to the same
     * signed URL without a CSRF token (see the VerifyCsrfToken exception in
     * routes/web.php).
     */
    public function store(Request $request, User $user)
    {
        if ($user->broadcast_opt_out_at === null) {
            $user->forceFill(['broadcast_opt_out_at' => now()])->save();

            Log::info('[Broadcast] Provider unsubscribed', ['user_id' => $user->id]);
        }

        // One-click clients expect a bare 200, not a rendered page.
        if ($request->expectsJson() || $request->header('List-Unsubscribe') !== null) {
            return response()->noContent();
        }

        return view('broadcast.unsubscribe', [
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
        $user->forceFill(['broadcast_opt_out_at' => null])->save();

        return view('broadcast.unsubscribe', [
            'user' => $user,
            'alreadyOptedOut' => false,
            'justResubscribed' => true,
        ]);
    }
}
