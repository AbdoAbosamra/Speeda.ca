<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_provider_profiles')) {
            return;
        }

        $driver = DB::getDriverName();

        if (Schema::hasColumn('service_provider_profiles', 'category_id')) {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE `service_provider_profiles` MODIFY `category_id` BIGINT UNSIGNED NULL');
            }
        }

        if (Schema::hasColumn('service_provider_profiles', 'location_id')) {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE `service_provider_profiles` MODIFY `location_id` BIGINT UNSIGNED NULL');
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('service_provider_profiles')) {
            return;
        }

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            if (Schema::hasColumn('service_provider_profiles', 'category_id')) {
                DB::statement('ALTER TABLE `service_provider_profiles` MODIFY `category_id` BIGINT UNSIGNED NOT NULL');
            }
            if (Schema::hasColumn('service_provider_profiles', 'location_id')) {
                DB::statement('ALTER TABLE `service_provider_profiles` MODIFY `location_id` BIGINT UNSIGNED NOT NULL');
            }
        }
    }
};
