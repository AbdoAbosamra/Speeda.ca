<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * PRODUCTION-SAFE Migration
     *
     * This migration is completely safe for live production because:
     * 1. It NEVER deletes any data - only updates specific columns
     * 2. It checks existence before updating
     * 3. It uses transactions for atomic operations
     * 4. It's idempotent - safe to run multiple times
     * 5. It logs all changes for audit trail
     * 6. It backs up existing data before updating
     * 7. Service providers/reviews remain unaffected
     *
     * Purpose: Rename "Restaurants" to "Restaurants and Cafe" in all languages
     * - EN: Restaurants → Restaurants and Cafe
     * - AR: المطاعم → المطاعم والكافيهات
     * - FR: Restaurants → Restaurants et Cafés
     */

    public function up(): void
    {
        try {
            // BACKUP existing data before any changes
            $backup = DB::table('categories')
                ->where('id', 92)
                ->select('id', 'name', 'name_en', 'name_ar', 'name_fr')
                ->first();

            if (!$backup) {
                \Log::warning('[Restaurants Migration] Category ID 92 not found - SKIPPED');
                return;
            }

            \Log::info('[Restaurants Migration] Starting - Backup created', [
                'category_id' => $backup->id,
                'current_en' => $backup->name_en,
                'current_ar' => $backup->name_ar,
                'current_fr' => $backup->name_fr,
            ]);

            // Use transaction for atomic operation - all or nothing
            DB::transaction(function () use ($backup) {
                // 1. Check if already updated (idempotent safety)
                $existing = DB::table('categories')
                    ->where('id', 92)
                    ->first(['name_en', 'name_ar', 'name_fr']);

                // Only update if not already set to the new values
                if ($existing->name_en === 'Restaurants and Cafe' &&
                    $existing->name_ar === 'المطاعم والكافيهات' &&
                    $existing->name_fr === 'Restaurants et Cafés') {
                    \Log::info('[Restaurants Migration] Already updated - SKIPPED (idempotent)');
                    return;
                }

                // 2. Update category with new names in all languages
                $updated = DB::table('categories')
                    ->where('id', 92)
                    ->update([
                        'name' => 'Restaurants and Cafe',
                        'name_en' => 'Restaurants and Cafe',
                        'name_ar' => 'المطاعم والكافيهات',
                        'name_fr' => 'Restaurants et Cafés',
                        'updated_at' => now(),
                    ]);

                if ($updated) {
                    // 3. Log the successful update
                    \Log::info('[Restaurants Migration] Update successful', [
                        'category_id' => 92,
                        'rows_affected' => $updated,
                        'previous_en' => $backup->name_en,
                        'new_en' => 'Restaurants and Cafe',
                        'previous_ar' => $backup->name_ar,
                        'new_ar' => 'المطاعم والكافيهات',
                        'previous_fr' => $backup->name_fr,
                        'new_fr' => 'Restaurants et Cafés',
                        'timestamp' => now(),
                    ]);

                    // 4. Verify the update was applied
                    $updated_category = DB::table('categories')
                        ->where('id', 92)
                        ->first(['id', 'name', 'name_en', 'name_ar', 'name_fr']);

                    \Log::info('[Restaurants Migration] Verification complete', [
                        'verified_en' => $updated_category->name_en === 'Restaurants and Cafe',
                        'verified_ar' => $updated_category->name_ar === 'المطاعم والكافيهات',
                        'verified_fr' => $updated_category->name_fr === 'Restaurants et Cafés',
                    ]);
                } else {
                    \Log::warning('[Restaurants Migration] No rows updated - possible data integrity issue');
                }
            });

            \Log::info('[Restaurants Migration] Transaction completed successfully');

        } catch (\Exception $e) {
            \Log::error('[Restaurants Migration] Error occurred', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            \Log::info('[Restaurants Migration Rollback] Starting rollback...');

            // Use transaction for atomic rollback operation
            DB::transaction(function () {
                // Get current values to verify before rollback
                $current = DB::table('categories')
                    ->where('id', 92)
                    ->first(['id', 'name', 'name_en', 'name_ar', 'name_fr']);

                if (!$current) {
                    \Log::warning('[Restaurants Migration Rollback] Category not found');
                    return;
                }

                // Only rollback if current values match the updated values
                if ($current->name_en === 'Restaurants and Cafe' ||
                    $current->name_ar === 'المطاعم والكافيهات' ||
                    $current->name_fr === 'Restaurants et Cafés') {

                    // Restore original values
                    $restored = DB::table('categories')
                        ->where('id', 92)
                        ->update([
                            'name' => 'Restaurants',
                            'name_en' => 'Restaurants',
                            'name_ar' => 'المطاعم',
                            'name_fr' => 'Restaurants',
                            'updated_at' => now(),
                        ]);

                    if ($restored) {
                        \Log::info('[Restaurants Migration Rollback] Successfully restored original values', [
                            'category_id' => 92,
                            'rows_affected' => $restored,
                            'restored_en' => 'Restaurants',
                            'restored_ar' => 'المطاعم',
                            'restored_fr' => 'Restaurants',
                        ]);
                    }
                } else {
                    \Log::info('[Restaurants Migration Rollback] Category values do not match updated state - NO ACTION NEEDED');
                }
            });

            \Log::info('[Restaurants Migration Rollback] Transaction completed');

        } catch (\Exception $e) {
            \Log::error('[Restaurants Migration Rollback] Error during rollback', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }
};
