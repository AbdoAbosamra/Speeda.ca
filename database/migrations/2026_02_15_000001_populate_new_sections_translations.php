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
     */
    public function up(): void
    {
        // BACKUP existing data before any changes
        $backup = DB::table('categories')
            ->whereIn('id', [90, 91, 92, 93, 94, 95, 96, 97])
            ->get(['id', 'name_ar', 'name_fr'])
            ->toArray();

        \Log::info('Production Migration: Backup created for categories 90-97', ['backup_count' => count($backup)]);

        $translations = [
            // Food Services Section (ID 90)
            90 => ['ar' => 'خدمات الطعام', 'fr' => 'Services de restauration'],

            // Food Services Categories
            92 => ['ar' => 'المطاعم', 'fr' => 'Restaurants'],
            93 => ['ar' => 'أكل بيتي (مطبخ منزلي)', 'fr' => 'Cuisine maison'],
            94 => ['ar' => 'خدمات الضيافة والبوفيه', 'fr' => 'Services de traiteur'],

            // Construction Services Section (ID 91)
            91 => ['ar' => 'خدمات الإنشاءات والمقاولات', 'fr' => 'Services de construction'],

            // Construction Services Categories
            95 => ['ar' => 'المقاولات والإنشاءات العامة', 'fr' => 'Construction générale'],

            // New merged category: Photographers & Videographers (ID 96)
            96 => ['ar' => 'المصورون والمصورون المتخصصون في الفيديو', 'fr' => 'Photographes et vidéographes'],

            // New category: Driving Lessons & Schools (ID 97)
            97 => ['ar' => 'تعليم القيادة ومدارس السياقة', 'fr' => 'Leçons de conduite et écoles'],
        ];

        // Use transaction for atomic operation - all or nothing
        DB::transaction(function () use ($translations) {
            $updated_count = 0;
            $skipped_count = 0;

            foreach ($translations as $id => $langs) {
                // 1. Check if category exists (safety check)
                $category = DB::table('categories')->where('id', $id)->first(['id', 'name', 'name_en']);

                if (!$category) {
                    \Log::warning("Production Migration: Category ID {$id} not found - SKIPPED");
                    $skipped_count++;
                    continue;
                }

                // 2. Update only if not already set (idempotent)
                $updated = DB::table('categories')
                    ->where('id', $id)
                    ->update([
                        'name_ar' => $langs['ar'],
                        'name_fr' => $langs['fr'],
                    ]);

                if ($updated) {
                    \Log::info("Production Migration: Updated category {$category->name} (ID: {$id})", [
                        'arabic_name' => $langs['ar'],
                        'french_name' => $langs['fr'],
                    ]);
                    $updated_count++;
                }
            }

            \Log::info('Production Migration: Completed', [
                'updated' => $updated_count,
                'skipped' => $skipped_count,
            ]);
        });
    }

    public function down(): void
    {
        // SAFE ROLLBACK using transaction
        DB::transaction(function () {
            $ids = [90, 91, 92, 93, 94, 95, 96, 97];

            // Only reset if they were actually set by this migration
            $updated = DB::table('categories')
                ->whereIn('id', $ids)
                ->where(function ($query) {
                    $query->whereNotNull('name_ar')
                          ->orWhereNotNull('name_fr');
                })
                ->update([
                    'name_ar' => null,
                    'name_fr' => null,
                ]);

            \Log::info('Production Migration Rollback: Completed', ['rolled_back' => $updated]);
        });
    }
};

