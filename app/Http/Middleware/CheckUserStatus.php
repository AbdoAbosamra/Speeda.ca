<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Safe check: Only verify is_active if column exists (graceful degradation)
        if (Schema::hasColumn('users', 'is_active')) {
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
}
