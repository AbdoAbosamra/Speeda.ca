<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lightweight presence heartbeat: refreshes the authenticated user's
 * last_seen_at at most once per minute to avoid writing on every request.
 */
class TrackUserPresence
{
    /** Only write again after this many seconds since the last heartbeat. */
    private const HEARTBEAT_INTERVAL_SECONDS = 60;

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            $lastSeen = $user->last_seen_at;

            if ($lastSeen === null || $lastSeen->diffInSeconds(now()) >= self::HEARTBEAT_INTERVAL_SECONDS) {
                $user->forceFill(['last_seen_at' => now()])->saveQuietly();
            }
        }

        return $next($request);
    }
}
