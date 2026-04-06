<?php

namespace App\Actions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
        $payload = $this->buildAnalyticsPayload($providerId, 'view', $sessionHash, $now);
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

    /**
     * Build an insert payload that is compatible with both legacy and current
     * analytics schemas without storing raw visitor IP addresses.
     */
    private function buildAnalyticsPayload(int $providerId, string $actionType, string $sessionHash, $timestamp): array
    {
        $columns = $this->getAnalyticsColumns();
        $payload = [];

        if (in_array('provider_id', $columns, true)) {
            $payload['provider_id'] = $providerId;
        }

        if (in_array('action_type', $columns, true)) {
            $payload['action_type'] = $actionType;
        }

        if (in_array('session_hash', $columns, true)) {
            $payload['session_hash'] = $sessionHash;
        } elseif (in_array('ip_address', $columns, true)) {
            $payload['ip_address'] = 'hash:' . substr($sessionHash, 0, 40);
        }

        if (in_array('created_at', $columns, true)) {
            $payload['created_at'] = $timestamp;
        }

        if (in_array('updated_at', $columns, true)) {
            $payload['updated_at'] = $timestamp;
        }

        return isset($payload['provider_id'], $payload['action_type']) ? $payload : [];
    }

    private function getAnalyticsColumns(): array
    {
        return Cache::rememberForever('analytics_table_columns', function () {
            return Schema::hasTable('analytics')
                ? Schema::getColumnListing('analytics')
                : [];
        });
    }
}
