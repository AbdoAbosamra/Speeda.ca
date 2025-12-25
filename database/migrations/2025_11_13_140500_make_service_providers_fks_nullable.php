<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Make `category_id` and `location_id` nullable to avoid insertion errors when controller
     * creates a minimal ServiceProvider record without these optional foreign keys.
     *
     * Note: this migration uses raw ALTER TABLE statements for MySQL. For SQLite this migration
     * will skip changes (SQLite requires table rebuilds).
     */
    public function up(): void
    {
        if (! Schema::hasTable('service_providers')) {
            return;
        }

        $driver = DB::getDriverName();

        if (Schema::hasColumn('service_providers', 'category_id')) {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE `service_providers` MODIFY `category_id` BIGINT UNSIGNED NULL');
            }
            // For SQLite we skip: altering column nullability needs table rebuild
        }

        if (Schema::hasColumn('service_providers', 'location_id')) {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE `service_providers` MODIFY `location_id` BIGINT UNSIGNED NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     * Revert the columns to NOT NULL (only for MySQL).
     */
    public function down(): void
    {
        if (! Schema::hasTable('service_providers')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            if (Schema::hasColumn('service_providers', 'category_id')) {
                DB::statement('ALTER TABLE `service_providers` MODIFY `category_id` BIGINT UNSIGNED NOT NULL');
            }

            if (Schema::hasColumn('service_providers', 'location_id')) {
                DB::statement('ALTER TABLE `service_providers` MODIFY `location_id` BIGINT UNSIGNED NOT NULL');
            }
        }
    }
};
