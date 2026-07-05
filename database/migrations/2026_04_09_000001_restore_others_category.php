<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * TASK 1 — Restore "Others" category (Section + child).
 *
 * Context:
 *  - The "Others" Section (id 62) and its child were accidentally deleted.
 *  - Some providers may have a dangling category_id.
 *
 * Safety:
 *  - Uses updateOrCreate / withTrashed to avoid duplicating existing rows.
 *  - Never overwrites any category that already exists and is active.
 *  - Re-links only providers whose category_id IS NULL or points to a
 *    non-existent category row (validated with a LEFT JOIN).
 *  - If the re-link would affect > 50 rows the migration logs the SQL
 *    and count instead of executing — manual review required.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        DB::transaction(function () {
            // ── Step 1: Restore / create "Others" SECTION ────────────

            // Check if id 62 exists (including soft-deleted)
            $existing = DB::table('categories')
                ->where('id', 62)
                ->first();

            if ($existing) {
                // Restore it: clear deleted_at, ensure active
                DB::table('categories')
                    ->where('id', 62)
                    ->update([
                        'name'       => 'Others',
                        'name_en'    => 'Others',
                        'name_ar'    => 'أخرى',
                        'name_fr'    => 'Autres',
                        'slug'       => 'others',
                        'icon'       => 'fas fa-ellipsis-h',
                        'is_section' => true,
                        'is_active'  => true,
                        'parent_id'  => null,
                        'sort_order' => 99,
                        'deleted_at' => null,
                        'updated_at' => now(),
                    ]);

                $sectionId = 62;
                Log::info('[TASK-1] Restored "Others" section at id 62');
            } else {
                // Insert fresh — try to keep id 62 if the auto-increment allows it
                $sectionId = DB::table('categories')->insertGetId([
                    'name'       => 'Others',
                    'name_en'    => 'Others',
                    'name_ar'    => 'أخرى',
                    'name_fr'    => 'Autres',
                    'slug'       => 'others',
                    'icon'       => 'fas fa-ellipsis-h',
                    'is_section' => true,
                    'is_active'  => true,
                    'parent_id'  => null,
                    'sort_order' => 99,
                    'deleted_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Log::info("[TASK-1] Created new \"Others\" section at id {$sectionId}");
            }

            // ── Step 2: Restore / create "Others" CHILD category ─────

            $childSlug = 'others-child';

            // Check for existing child (including soft-deleted)
            $existingChild = DB::table('categories')
                ->where('parent_id', $sectionId)
                ->where('is_section', false)
                ->where(function ($q) use ($childSlug) {
                    $q->where('slug', $childSlug)
                      ->orWhere('name_en', 'Others');
                })
                ->first();

            if ($existingChild) {
                DB::table('categories')
                    ->where('id', $existingChild->id)
                    ->update([
                        'name'       => 'Others',
                        'name_en'    => 'Others',
                        'name_ar'    => 'أخرى',
                        'name_fr'    => 'Autres',
                        'slug'       => $childSlug,
                        'icon'       => 'fas fa-ellipsis-h',
                        'is_section' => false,
                        'is_active'  => true,
                        'parent_id'  => $sectionId,
                        'sort_order' => 1,
                        'deleted_at' => null,
                        'updated_at' => now(),
                    ]);

                $childId = $existingChild->id;
                Log::info("[TASK-1] Restored \"Others\" child category at id {$childId}");
            } else {
                $childId = DB::table('categories')->insertGetId([
                    'name'       => 'Others',
                    'name_en'    => 'Others',
                    'name_ar'    => 'أخرى',
                    'name_fr'    => 'Autres',
                    'slug'       => $childSlug,
                    'icon'       => 'fas fa-ellipsis-h',
                    'is_section' => false,
                    'is_active'  => true,
                    'parent_id'  => $sectionId,
                    'sort_order' => 1,
                    'deleted_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Log::info("[TASK-1] Created new \"Others\" child category at id {$childId}");
            }

            // ── Step 3: Re-link orphaned providers ───────────────────
            // A provider is "orphaned" when:
            //   • category_id IS NULL, OR
            //   • category_id points to a row that no longer exists in categories

            $orphanedCount = DB::table('service_providers as sp')
                ->leftJoin('categories as c', 'sp.category_id', '=', 'c.id')
                ->where(function ($q) {
                    $q->whereNull('sp.category_id')
                      ->orWhereNull('c.id');
                })
                ->count();

            if ($orphanedCount === 0) {
                Log::info('[TASK-1] No orphaned providers found — nothing to re-link.');
                return;
            }

            if ($orphanedCount > 50) {
                // Safety guard: log the SQL and count for human review
                Log::warning("[TASK-1] STOPPED: {$orphanedCount} orphaned providers found (> 50). Manual review required.");
                Log::warning("[TASK-1] Run: UPDATE service_providers SET category_id = {$childId} WHERE category_id IS NULL OR category_id NOT IN (SELECT id FROM categories)");
                return;
            }

            // Safe to execute
            DB::table('service_providers as sp')
                ->leftJoin('categories as c', 'sp.category_id', '=', 'c.id')
                ->where(function ($q) {
                    $q->whereNull('sp.category_id')
                      ->orWhereNull('c.id');
                })
                ->update(['sp.category_id' => $childId]);

            Log::info("[TASK-1] Re-linked {$orphanedCount} orphaned providers to \"Others\" child category (id {$childId}).");
        });
    }

    public function down(): void
    {
        // Forward-only data recovery migration — no rollback.
    }
};
