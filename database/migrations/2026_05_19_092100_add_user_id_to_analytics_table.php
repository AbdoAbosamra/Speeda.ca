<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ADMIN_EXCLUSION POLICY — DO NOT REMOVE
     *
     * This migration adds user_id to the analytics table so that
     * admin-generated activity can be identified and excluded from
     * all provider analytics counts.
     *
     * After this migration runs, the ProviderAnalytics model's
     * GlobalScope 'exclude_admin_activity' automatically activates
     * (it checks Cache::get('analytics_has_user_id')).
     *
     * Enforcement layers:
     * 1. TrackProviderViewAction: checks auth()->user()->isAdmin() → returns early
     * 2. TrackProviderClickAction: same guard
     * 3. All raw DB queries in services use whereNotIn('user_id', $adminIds)
     * 4. ProviderAnalytics model: GlobalScope excludes admin user_ids
     */
    public function up(): void
    {
        if (!Schema::hasColumn('analytics', 'user_id')) {
            Schema::table('analytics', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('provider_id');
                $table->index(['user_id', 'action_type', 'created_at'], 'analytics_user_action_date_idx');
            });
        }

        Cache::forever('analytics_has_user_id', true);
    }

    public function down(): void
    {
        Cache::forget('analytics_has_user_id');

        Schema::table('analytics', function (Blueprint $table) {
            $table->dropIndex('analytics_user_action_date_idx');
            $table->dropColumn('user_id');
        });
    }
};
