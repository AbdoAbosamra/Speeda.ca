<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Add calculated_rating column to service_providers table
 * 
 * PERFORMANCE OPTIMIZATION:
 * Replaces the live subquery calculation with a pre-calculated cached column.
 * The calculated_rating is updated only when a review changes status.
 * 
 * SAFETY:
 * - ADDITIVE only (adds column, does not modify existing)
 * - Backfills existing data safely
 * - Rollback removes only the new column
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // STEP 1: Add the new column (additive, no data loss)
        Schema::table('service_providers', function (Blueprint $table) {
            $table->decimal('calculated_rating', 3, 2)
                ->default(0.00)
                ->after('rating')
                ->comment('Pre-calculated average rating from active reviews, updated on review status change');
        });

        // STEP 2: Backfill existing data safely
        // Use a single UPDATE with subquery to populate calculated_rating
        // This is more efficient than iterating through records
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement('
                UPDATE service_providers sp
                SET calculated_rating = COALESCE(
                    (SELECT AVG(r.rating) 
                     FROM service_provider_reviews r 
                     WHERE r.service_provider_id = sp.id 
                     AND r.is_active = 1),
                    0
                )
            ');
        }

        // STEP 3: Add index for sorting performance
        Schema::table('service_providers', function (Blueprint $table) {
            $table->index('calculated_rating', 'idx_service_providers_calculated_rating');
        });
    }

    /**
     * Reverse the migrations.
     * SAFETY: Only removes the new column, preserves all existing data
     */
    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            // Drop index first
            $table->dropIndex('idx_service_providers_calculated_rating');
            // Then drop column
            $table->dropColumn('calculated_rating');
        });
    }
};
