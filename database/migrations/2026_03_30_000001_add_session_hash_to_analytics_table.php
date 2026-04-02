<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PRIVACY COMPLIANCE: Stop storing raw IP addresses.
     * - Add session_hash column for spam-protection deduplication.
     * - Make ip_address nullable (preserves historical data, no writes going forward).
     * - Add composite index for efficient deduplication lookups.
     *
     * PRODUCTION-SAFE: Additive only, no data loss.
     */
    public function up(): void
    {
        Schema::table('analytics', function (Blueprint $table) {
            // New privacy-compliant identifier for deduplication
            $table->string('session_hash', 64)->nullable()->after('ip_address');

            // Composite index for deduplication: "has this session already viewed this provider today?"
            $table->index(['provider_id', 'session_hash', 'created_at'], 'analytics_provider_session_created_idx');
        });

        // Make ip_address nullable (separate statement for SQLite compatibility)
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('analytics', function (Blueprint $table) {
                $table->string('ip_address', 45)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('analytics', function (Blueprint $table) {
            $table->dropIndex('analytics_provider_session_created_idx');
            $table->dropColumn('session_hash');
        });
    }
};
