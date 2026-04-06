<?php

namespace App\Actions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TrackProviderClickAction
{
    /**
     * Track a provider click event (e.g. WhatsApp button).
     *
     * PRIVACY: No IP address is stored. A hashed session fingerprint
     * is recorded for analytics purposes only.
     */
    public function execute(int $providerId, string $actionType): bool
    {
        $sessionHash = $this->generateSessionHash();
        $now = now();
        $payload = $this->buildAnalyticsPayload($providerId, $actionType, $sessionHash, $now);

        if (empty($payload)) {
            return false;
        }

        try {
            DB::table('analytics')->insert($payload);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Provider click analytics insert failed', [
                'provider_id' => $providerId,
                'action_type' => $actionType,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Generate a privacy-safe session fingerprint hash.
     */
    private function generateSessionHash(): string
    {
        $sessionId = session()->getId();
        $userAgent = request()->userAgent() ?? 'unknown';

        if (empty($sessionId)) {
            return hash('sha256', 'anonymous|' . $userAgent);
        }

        return hash('sha256', $sessionId . '|' . $userAgent);
    }

    /**
     * Build an insert payload for whichever analytics schema is currently deployed.
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
