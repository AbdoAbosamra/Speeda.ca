<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Category Cache Service
 * 
 * PERFORMANCE: Caches the category tree in Redis to avoid 30+ DB queries per page load.
 * 
 * Features:
 * - Redis-first caching with DB fallback
 * - Locale-aware cache keys (separate cache per language)
 * - Automatic invalidation on admin changes
 * - 24-hour TTL (categories rarely change)
 */
class CategoryCacheService
{
    /**
     * Cache key prefix for the full category tree (sections → groups → professions)
     */
    protected const CACHE_KEY_TREE = 'speeda.category_tree';
    
    /**
     * Cache key prefix for terminal categories (professions only)
     */
    protected const CACHE_KEY_TERMINAL = 'speeda.category_terminal';
    
    /**
     * Cache key prefix for filter groups (groups for dropdown filters)
     */
    protected const CACHE_KEY_FILTER_GROUPS = 'speeda.category_filter_groups';
    
    /**
     * TTL: 24 hours (categories rarely change)
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
     * Get the full category tree (sections with nested children).
     * Used on homepage and category listing pages.
     *
     * @return Collection
     */
    public function getCategoryTree(): Collection
    {
        return $this->rememberWithFallback($this->getLocaleKey(self::CACHE_KEY_TREE), function () {
            return Category::where('is_section', true)
                ->where('is_active', true)
                ->where('slug', '!=', 'others-1')
                ->with(['children' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->with(['children' => function ($childQuery) {
                            $childQuery->where('is_active', true)
                                ->orderBy('sort_order')
                                ->orderBy('name');
                        }]);
                }])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get terminal categories (professions) with parent chain.
     * Used in provider registration and profile forms.
     *
     * @return Collection
     */
    public function getTerminalCategories(): Collection
    {
        return $this->rememberWithFallback($this->getLocaleKey(self::CACHE_KEY_TERMINAL), function () {
            return Category::with('parent.parent')
                ->terminal()
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get filter groups (categories for dropdown filters).
     * Used in provider listing sidebar.
     *
     * @return Collection
     */
    public function getFilterGroups(): Collection
    {
        return $this->rememberWithFallback($this->getLocaleKey(self::CACHE_KEY_FILTER_GROUPS), function () {
            return Category::with('children')
                ->filterGroups()
                ->get()
                ->sortBy('translated_name')
                ->values();
        });
    }

    /**
     * Invalidate all category caches for ALL locales.
     * Called by admin when categories are created/updated/deleted.
     *
     * @return void
     */
    public function invalidateCache(): void
    {
        $baseKeys = [
            self::CACHE_KEY_TREE,
            self::CACHE_KEY_TERMINAL,
            self::CACHE_KEY_FILTER_GROUPS,
        ];

        $locales = config('app.supported_locales', ['en', 'ar', 'fr']);
        $localeCodes = is_array($locales) && !isset($locales[0]) ? array_keys($locales) : $locales;

        foreach ($baseKeys as $baseKey) {
            foreach ($localeCodes as $locale) {
                $key = $baseKey . '_' . $locale;
                try {
                    // Try Redis first if available
                    if (extension_loaded('redis')) {
                        Cache::store('redis')->forget($key);
                    }
                    // Also clear from default cache store
                    Cache::forget($key);
                    Log::debug("Category cache invalidated: {$key}");
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
