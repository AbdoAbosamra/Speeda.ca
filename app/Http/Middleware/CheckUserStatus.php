<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     * 
     * Check if user account is active. Deactivated users are logged out
     * and redirected with an error message.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Safe check: only verify is_active if the column exists (graceful
        // degradation). The lookup is cached because this middleware runs on
        // EVERY web request and Schema::hasColumn issues a metadata query each
        // time it is called.
        if (self::usersTableHasActiveColumn()) {
            // Check if user is deactivated
            if (!$user->is_active) {
                // Log the attempt for security auditing
                \Illuminate\Support\Facades\Log::warning('Deactivated user attempted access', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'route' => $request->route()->getName(),
                    'ip' => $request->ip(),
                ]);

                // Logout the user
                Auth::logout();
                
                // Invalidate session
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect with error message (Blade redirect only, no JSON)
                return redirect()->route('login')
                    ->with('error', __('auth.account_disabled'));
            }
        }

        return $next($request);
    }

    /**
     * Does the users table have an is_active column?
     *
     * Resolved once per process and then cached for a day, so a hot request
     * path never pays for a schema introspection query.
     */
    private static function usersTableHasActiveColumn(): bool
    {
        static $resolved = null;

        if ($resolved !== null) {
            return $resolved;
        }

        try {
            $resolved = Cache::remember(
                'schema.users.has_is_active',
                now()->addDay(),
                fn () => Schema::hasColumn('users', 'is_active')
            );
        } catch (\Throwable $e) {
            // Never let a cache/schema failure block authentication.
            $resolved = Schema::hasColumn('users', 'is_active');
        }

        return $resolved;
    }
}
