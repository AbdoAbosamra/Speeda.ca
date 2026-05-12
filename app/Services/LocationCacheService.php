<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Location Cache Service
 * 
 * PERFORMANCE: Caches the location list in Redis to avoid repeated DB queries.
 * 
 * Features:
 * - Redis-first caching with DB fallback
 * - Locale-aware cache keys (separate cache per language)
 * - Automatic invalidation on admin changes
 * - 24-hour TTL (locations rarely change)
 */
class LocationCacheService
{
    /**
     * Cache key prefix for active locations (frontend)
     */
    protected const CACHE_KEY_ACTIVE = 'speeda.location_active';
    
    /**
     * Cache key prefix for all locations (admin forms)
     */
    protected const CACHE_KEY_ALL = 'speeda.location_all';
    
    /**
     * TTL: 24 hours (locations rarely change)
     */
    protected const TTL = 86400;

    /**
     * Get locale-specific cache key.
     * Ensures each language has its own cached version.
     *
     * @param string $key Base cache key
     * @return string Locale-specific cache key
     */
    protected function getLocaleKey(string $key): string
    {
        return $key . '_' . app()->getLocale();
    }

    /**
     * Get active locations for frontend.
     * Used in dropdowns, filters, and public pages.
     *
     * @return Collection
     */
    public function getActiveLocations(): Collection
    {
        return $this->rememberWithFallback($this->getLocaleKey(self::CACHE_KEY_ACTIVE), function () {
            return Location::where('is_active', true)
                ->orderBy('city')
                ->get();
        });
    }

    /**
     * Get all locations (including inactive).
     * Used in admin panel and provider edit forms.
     *
     * @return Collection
     */
    public function getAllLocations(): Collection
    {
        return $this->rememberWithFallback($this->getLocaleKey(self::CACHE_KEY_ALL), function () {
            return Location::orderBy('city')->get();
        });
    }

    /**
     * Invalidate all location caches for ALL locales.
     * Called by admin when locations are created/updated/deleted.
     *
     * @return void
     */
    public function invalidateCache(): void
    {
        $baseKeys = [
            self::CACHE_KEY_ACTIVE,
            self::CACHE_KEY_ALL,
        ];

        $locales = config('app.supported_locales', ['en', 'ar', 'fr']);

        foreach ($baseKeys as $baseKey) {
            foreach ($locales as $locale) {
                $key = $baseKey . '_' . $locale;
                try {
                    // Try Redis first if available
                    if (extension_loaded('redis')) {
                        Cache::store('redis')->forget($key);
                    }
                    // Also clear from default cache store
                    Cache::forget($key);
                    Log::debug("Location cache invalidated: {$key}");
                } catch (\Exception $e) {
                    Log::warning("Failed to invalidate cache key {$key}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Cache::remember() with Redis fallback to direct DB query.
     * If Redis is unavailable, always return fresh data from database.
     *
     * @param string $key
     * @param callable $callback
     * @return Collection
     */
    protected function rememberWithFallback(string $key, callable $callback): Collection
    {
        try {
            // Try Redis first if available, fall back to default cache store
            try {
                if (extension_loaded('redis')) {
                    return Cache::store('redis')->remember($key, self::TTL, $callback);
                }
            } catch (\Exception $redisError) {
                Log::warning('Redis cache failed, using default cache store', [
                    'key' => $key,
                    'error' => $redisError->getMessage(),
                ]);
            }

            // Fall back to default cache store (database)
            return Cache::remember($key, self::TTL, $callback);
        } catch (\Exception $e) {
            Log::warning('Cache unavailable, falling back to direct DB query', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            try {
                return $callback();
            } catch (\Exception $dbError) {
                Log::error('Database query failed during cache fallback', [
                    'key' => $key,
                    'error' => $dbError->getMessage(),
                ]);
                return collect();
            }
        }
    }
}
