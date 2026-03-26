<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add composite index for filter queries on service_providers table.
 * Production-safe: additive only, no data changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            // Composite index for category + location filter combinations
            // This speeds up the most common filter query pattern
            $table->index(['category_id', 'location_id'], 'sp_category_location_idx');
        });
    }

    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropIndex('sp_category_location_idx');
        });
    }
};
