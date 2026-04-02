<?php

namespace App\Actions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TrackProviderViewAction
{
    /**
     * Track a profile view with session-based deduplication.
     *
     * PRIVACY: No IP address is stored. Deduplication uses a hashed
     * fingerprint of the session ID + User-Agent, which cannot be
     * reversed to identify a person.
     */
    public function execute(int $providerId): bool
    {
        $sessionHash = $this->generateSessionHash();

        if (empty($sessionHash)) {
            return false;
        }

        // Deduplicate profile views by session fingerprint within 24 hours.
        $cacheKey = sprintf('provider_view_%d_%s', $providerId, $sessionHash);
        $ttl = now()->addHours(24);

        $shouldInsert = Cache::add($cacheKey, 1, $ttl);
        if (!$shouldInsert) {
            return false;
        }

        $now = now();

        DB::table('analytics')->insert([
            'provider_id' => $providerId,
            'action_type' => 'view',
            'session_hash' => $sessionHash,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return true;
    }

    /**
     * Generate a privacy-safe session fingerprint hash.
     * Combines session ID + User-Agent into a non-reversible SHA-256 hash.
     */
    private function generateSessionHash(): string
    {
        $sessionId = session()->getId();
        $userAgent = request()->userAgent() ?? 'unknown';

        if (empty($sessionId)) {
            return '';
        }

        return hash('sha256', $sessionId . '|' . $userAgent);
    }
}
