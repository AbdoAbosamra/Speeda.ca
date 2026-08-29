<?php

namespace App\Http\Middleware;

use App\Services\GoogleAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hides the Google sign-in routes when no credentials are configured.
 *
 * A middleware rather than a check in the controller's constructor: Laravel
 * instantiates controllers while gathering route middleware, so aborting from
 * a constructor takes `route:list` down with it.
 */
class EnsureGoogleAuthEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(GoogleAuthService::isEnabled(), 404);

        return $next($request);
    }
}
