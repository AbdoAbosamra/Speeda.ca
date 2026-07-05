<?php

namespace App\Actions;

use App\Actions\Concerns\BuildsAnalyticsPayload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrackProviderViewAction
{
    use BuildsAnalyticsPayload;

    /**
     * Track a profile view with session-based deduplication.
     *
     * PRIVACY: No IP address is stored. Deduplication uses a hashed fingerprint
     * of the session ID + User-Agent, which cannot be reversed to identify a
     * person. Context (category, location, source page, locale, device bucket)
     * is recorded so conversion rates can be computed per category/location.
     *
     * @param  array{source_page?:string|null}  $context
     */
    public function execute(int $providerId, array $context = []): bool
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return false;
        }

        $sessionHash = $this->generateSessionHash();

        if (empty($sessionHash)) {
            return false;
        }

        // Deduplicate profile views by session fingerprint within 24 hours.
        $cacheKey = sprintf('provider_view_%d_%s', $providerId, $sessionHash);
        $ttl = now()->addHours(24);

        $shouldInsert = Cache::add($cacheKey, 1, $ttl);
        if (! $shouldInsert) {
            return false;
        }

        $now = now();
        $userId = Auth::id();
        $payload = $this->buildAnalyticsPayload($providerId, 'view', $sessionHash, $now, $userId, $context);
        if (empty($payload)) {
            return false;
        }

        try {
            DB::table('analytics')->insert($payload);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Provider view analytics insert failed', [
                'provider_id' => $providerId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
