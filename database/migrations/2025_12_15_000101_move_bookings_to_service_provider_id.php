<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'service_provider_id')) {
                $table->unsignedBigInteger('service_provider_id')->nullable()->after('id');
                $table->foreign('service_provider_id')
                    ->references('id')->on('service_providers')
                    ->onDelete('cascade');
                $table->index('service_provider_id');
            }
        });

        // Backfill service_provider_id using existing service_provider_profile_id via user_id linkage
        try {
            // This raw SQL should work on MySQL; for SQLite in tests, it will be skipped to avoid errors
            $driver = DB::getDriverName();
            if (in_array($driver, ['mysql', 'mariadb'])) {
                DB::statement("
                    UPDATE bookings b
                    JOIN service_provider_profiles spp ON b.service_provider_profile_id = spp.id
                    JOIN service_providers sp ON sp.user_id = spp.user_id
                    SET b.service_provider_id = sp.id
                    WHERE b.service_provider_id IS NULL
                ");
            }
        } catch (\Throwable $e) {
            // Best-effort backfill; ignore errors to keep migration resilient across drivers
        }

        // Note: We intentionally keep service_provider_profile_id for backward compatibility.
        // It can be dropped in a later cleanup migration once the codebase fully migrates.
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'service_provider_id')) {
                $table->dropForeign(['service_provider_id']);
                $table->dropIndex(['service_provider_id']);
                $table->dropColumn('service_provider_id');
            }
        });
    }
};
