<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the service_providers.is_active column.
 *
 * The earlier 2026_02_10_003837_add_is_active_to_service_providers_table
 * migration was committed with an empty body (`//`), so it recorded itself as
 * run without ever creating the column. Model code and admin tooling assumed the
 * column existed. This migration adds it for real and is guarded so it is safe
 * to run on databases where the column was patched in manually.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('service_providers', 'is_active')) {
            return;
        }

        Schema::table('service_providers', function (Blueprint $table) {
            // Default true so every existing provider stays visible.
            $table->boolean('is_active')->default(true)->after('is_featured');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('service_providers', 'is_active')) {
            return;
        }

        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropColumn('is_active');
        });
    }
};
