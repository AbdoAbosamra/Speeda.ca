<?php

namespace App\Http\Middleware;

use App\Models\Visitor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track GET requests to avoid counting form submissions
        if ($request->isMethod('get')) {
            try {
                $this->recordVisitor($request);
            } catch (\Exception $e) {
                // Silently fail - visitor tracking should not break the application
                \Illuminate\Support\Facades\Log::warning('Failed to track visitor', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $response;
    }

    /**
     * Record a visitor in the database.
     */
    protected function recordVisitor(Request $request): void
    {
        // GDPR-compliant hashing with salt using app key
        $ipHash = hash_hmac('sha256', $request->ip(), config('app.key'));
        $userAgentHash = hash_hmac('sha256', $request->userAgent(), config('app.key'));

        // Check if this visitor has already been recorded in the last 5 minutes
        $recentVisit = Visitor::where('ip_hash', $ipHash)
            ->where('user_agent_hash', $userAgentHash)
            ->where('visited_at', '>=', now()->subMinutes(5))
            ->exists();

        // Only record if this is a new visit (not within last 5 minutes)
        if (!$recentVisit) {
            Visitor::create([
                'ip_hash' => $ipHash,
                'user_agent_hash' => $userAgentHash,
                'path' => $request->path(),
                'referer' => $request->header('referer'),
                'user_id' => Auth::id(),
                'visited_at' => now(),
            ]);

            // Clear visitor stats cache to ensure fresh data
            \Illuminate\Support\Facades\Cache::forget('visitor_stats');
            \Illuminate\Support\Facades\Cache::forget('live_visitors_count');
        }
    }
}
