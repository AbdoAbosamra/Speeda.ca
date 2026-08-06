<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pins the admin panel to English.
 *
 * WHY
 * ---
 * The panel is intentionally English-only. Without this, SetLocale would apply
 * the user's site language to admin routes too, and because only part of the
 * panel goes through __('admin.*') the result was a half-translated screen:
 * Categories / Locations / Comments / Activity Logs rendered in Arabic while
 * Dashboard / Providers / Notifications / Analytics stayed hardcoded English —
 * and the layout flipped to dir="rtl" around English text.
 *
 * IMPORTANT
 * ---------
 * This only overrides the locale for the current request. It deliberately does
 * NOT write to the session, so an admin who browses the public site in Arabic
 * keeps that preference when they navigate back out of the panel.
 */
class ForceAdminLocale
{
    public const ADMIN_LOCALE = 'en';

    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale(self::ADMIN_LOCALE);

        return $next($request);
    }
}
