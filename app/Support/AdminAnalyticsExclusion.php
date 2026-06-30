<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Centralised, NULL-safe admin-activity exclusion for analytics queries.
 *
 * WHY THIS EXISTS
 * ---------------
 * Analytics rows store `user_id` which is NULL for anonymous (guest) visitors —
 * the vast majority of traffic. A naive `whereNotIn('user_id', $adminIds)` filters
 * those rows out, because in SQL `NULL NOT IN (...)` evaluates to NULL (not TRUE),
 * so every guest row silently disappears from the counts.
 *
 * This helper applies the correct predicate: keep the row when `user_id` is NULL
 * (guest) OR it is not an admin id. Admins are already blocked at write-time in the
 * tracking actions; this is the read-time safety net.
 */
class AdminAnalyticsExclusion
{
    /**
     * Cached list of admin user ids.
     *
     * @return array<int>
     */
    public static function adminIds(): array
    {
        return Cache::remember('admin_user_ids', 3600, fn () =>
            User::where('role', 'admin')->pluck('id')->toArray()
        );
    }

    /**
     * Apply NULL-safe admin exclusion to a query builder (Eloquent or DB).
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $query
     */
    public static function apply($query, string $column = 'user_id'): void
    {
        $adminIds = self::adminIds();

        if (empty($adminIds)) {
            return;
        }

        $query->where(function ($q) use ($column, $adminIds) {
            $q->whereNull($column)
              ->orWhereNotIn($column, $adminIds);
        });
    }
}
