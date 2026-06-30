<?php

namespace App\Actions\Concerns;

use App\Support\DeviceType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Shared analytics payload construction for the tracking actions.
 *
 * Builds an insert payload that is compatible with whichever analytics schema
 * is currently deployed (legacy ip_address, session_hash, and the newer context
 * columns) and enriches it with privacy-safe dimensions: category, location,
 * source page, locale and a coarse device bucket.
 *
 * PRIVACY: No raw IP address and no raw User-Agent are ever stored. The device
 * value is a non-identifying bucket (mobile/tablet/desktop/bot).
 */
trait BuildsAnalyticsPayload
{
    /**
     * Generate a privacy-safe session fingerprint hash (session id + UA, salted).
     */
    protected function generateSessionHash(): string
    {
        $sessionId = session()->getId();
        $userAgent = request()->userAgent() ?? 'unknown';

        if (empty($sessionId)) {
            return hash('sha256', config('app.analytics_salt') . 'anonymous|' . $userAgent);
        }

        return hash('sha256', config('app.analytics_salt') . $sessionId . '|' . $userAgent);
    }

    /**
     * Build the insert payload for the analytics row.
     *
     * @param  array{source_page?:string|null}  $context
     */
    protected function buildAnalyticsPayload(
        int $providerId,
        string $actionType,
        string $sessionHash,
        $timestamp,
        ?int $userId = null,
        array $context = []
    ): array {
        $columns = $this->getAnalyticsColumns();
        $payload = [];

        $has = fn (string $column) => in_array($column, $columns, true);

        if ($has('provider_id')) {
            $payload['provider_id'] = $providerId;
        }

        if ($has('user_id')) {
            $payload['user_id'] = $userId;
        }

        if ($has('action_type')) {
            $payload['action_type'] = $actionType;
        }

        if ($has('session_hash')) {
            $payload['session_hash'] = $sessionHash;
        } elseif ($has('ip_address')) {
            // Legacy schema: store a non-reversible token, never a real IP.
            $payload['ip_address'] = 'hash:' . substr($sessionHash, 0, 40);
        }

        // --- Context enrichment (only when the columns exist) ---
        if ($has('category_id') || $has('location_id')) {
            [$categoryId, $locationId] = $this->resolveProviderContext($providerId);

            if ($has('category_id')) {
                $payload['category_id'] = $categoryId;
            }
            if ($has('location_id')) {
                $payload['location_id'] = $locationId;
            }
        }

        if ($has('source_page')) {
            $payload['source_page'] = $this->normaliseSourcePage($context['source_page'] ?? null);
        }

        if ($has('locale')) {
            $payload['locale'] = substr((string) app()->getLocale(), 0, 5) ?: null;
        }

        if ($has('device_type')) {
            $payload['device_type'] = DeviceType::fromUserAgent(request()->userAgent());
        }

        if ($has('created_at')) {
            $payload['created_at'] = $timestamp;
        }

        if ($has('updated_at')) {
            $payload['updated_at'] = $timestamp;
        }

        return isset($payload['provider_id'], $payload['action_type']) ? $payload : [];
    }

    /**
     * Resolve (category_id, location_id) for a provider, cached to keep tracking fast.
     *
     * @return array{0:int|null,1:int|null}
     */
    protected function resolveProviderContext(int $providerId): array
    {
        return Cache::remember("provider_context_{$providerId}", now()->addHours(6), function () use ($providerId) {
            $row = DB::table('service_providers')
                ->where('id', $providerId)
                ->first(['category_id', 'location_id']);

            return [
                $row->category_id ?? null,
                $row->location_id ?? null,
            ];
        });
    }

    /**
     * Whitelist source page values so the column stays clean and bounded.
     */
    protected function normaliseSourcePage(?string $value): ?string
    {
        $value = strtolower(trim((string) $value));

        $allowed = [
            'provider_profile',
            'listing',
            'search',
            'category',
            'location',
            'home',
        ];

        return in_array($value, $allowed, true) ? $value : 'provider_profile';
    }

    protected function getAnalyticsColumns(): array
    {
        return Cache::rememberForever('analytics_table_columns', function () {
            return Schema::hasTable('analytics')
                ? Schema::getColumnListing('analytics')
                : [];
        });
    }
}
