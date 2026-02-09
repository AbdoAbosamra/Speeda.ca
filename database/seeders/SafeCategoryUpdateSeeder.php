<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SafeCategoryUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * SAFE UPDATE: This seeder only ADDS/UPDATES/DELETES specific categories
     * without affecting user associations or truncating the table.
     */
    public function run(): void
    {
        $timestamp = now();

        $this->command->info('🚀 Starting safe category updates...');
        $this->command->newLine();

        // ========================================
        // STEP 1: Create New Sections
        // ========================================
        $this->command->info('📁 Creating new sections...');

        $newSections = [
            [
                'id' => 90,
                'name' => 'Food Services',
                'slug' => 'food-services',
                'icon' => 'fas fa-utensils',
                'color' => '#ff6b6b',
                'sort_order' => 8
            ],
            [
                'id' => 91,
                'name' => 'Construction Services',
                'slug' => 'construction-services',
                'icon' => 'fas fa-hard-hat',
                'color' => '#feca57',
                'sort_order' => 9
            ]
        ];

        foreach ($newSections as $section) {
            $exists = DB::table('categories')->where('id', $section['id'])->exists();

            if (!$exists) {
                DB::table('categories')->insert(array_merge($section, [
                    'is_section' => 1,
                    'is_active' => 1,
                    'parent_id' => null,
                    'description' => $section['name'] . ' directory',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]));
                $this->command->info("  ✅ Created section: {$section['name']}");
            } else {
                $this->command->warn("  ⚠️  Section already exists: {$section['name']}");
            }
        }

        $this->command->newLine();

        // ========================================
        // STEP 2: Add New Categories
        // ========================================
        $this->command->info('➕ Adding new categories...');

        $newCategories = [
            // Food Services categories
            ['id' => 92, 'name' => 'Restaurants', 'slug' => 'restaurants', 'parent_id' => 90, 'icon' => 'fas fa-store', 'color' => '#ff6b6b', 'sort_order' => 1],
            ['id' => 93, 'name' => 'Home Kitchen', 'slug' => 'home-kitchen', 'parent_id' => 90, 'icon' => 'fas fa-home', 'color' => '#ff6b6b', 'sort_order' => 2],
            ['id' => 94, 'name' => 'Catering Services', 'slug' => 'catering-services-food', 'parent_id' => 90, 'icon' => 'fas fa-concierge-bell', 'color' => '#ff6b6b', 'sort_order' => 3],

            // Construction Services categories
            ['id' => 95, 'name' => 'General Construction', 'slug' => 'general-construction', 'parent_id' => 91, 'icon' => 'fas fa-building', 'color' => '#feca57', 'sort_order' => 1],
            // Carpentry will be moved (ID 17)

            // Personal & Lifestyle - New merged category
            ['id' => 96, 'name' => 'Photographers & Videographers', 'slug' => 'photographers-videographers', 'parent_id' => 4, 'icon' => 'fas fa-camera-retro', 'color' => '#fd7e14', 'sort_order' => 12],

            // Professional & Business - New category
            ['id' => 97, 'name' => 'Driving Lessons & Schools', 'slug' => 'driving-lessons-schools', 'parent_id' => 3, 'icon' => 'fas fa-car', 'color' => '#007bff', 'sort_order' => 13],
        ];

        foreach ($newCategories as $category) {
            $exists = DB::table('categories')->where('id', $category['id'])->exists();

            if (!$exists) {
                DB::table('categories')->insert(array_merge($category, [
                    'is_section' => 0,
                    'is_active' => 1,
                    'description' => 'Professional ' . $category['name'] . ' services in Laval, Montreal, Ottawa, Gatineau.',
                    'meta_title' => $category['name'] . ' | Professional Services',
                    'meta_description' => 'Professional ' . $category['name'] . ' services in Laval, Montreal, Ottawa, Gatineau.',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]));
                $this->command->info("  ✅ Added: {$category['name']}");
            } else {
                $this->command->warn("  ⚠️  Already exists: {$category['name']}");
            }
        }

        $this->command->newLine();

        // ========================================
        // STEP 3: Update Existing Categories
        // ========================================
        $this->command->info('🔄 Updating existing categories...');

        // Rename "Electrical Technicians" to "Electrical Services"
        DB::table('categories')
            ->where('id', 20)
            ->update([
                'name' => 'Electrical Services',
                'slug' => 'electrical-services',
                'updated_at' => $timestamp
            ]);
        $this->command->info('  ✅ Renamed: Electrical Technicians → Electrical Services');

        // Move "Carpentry Services" to Construction section (parent_id = 91)
        DB::table('categories')
            ->where('id', 17)
            ->update([
                'parent_id' => 91,
                'color' => '#feca57',
                'sort_order' => 2,
                'updated_at' => $timestamp
            ]);
        $this->command->info('  ✅ Moved: Carpentry Services → Construction Services');

        $this->command->newLine();
        // ========================================
// STEP 3.5: Ensure Others Section is Last
// ========================================
$this->command->info('🔧 Ensuring "Others" section appears last...');

// خلي Others يظهر بعد كل الأقسام
// احسب أعلى sort_order للأقسام + 1
$maxSectionOrder = DB::table('categories')
    ->where('is_section', 1)
    ->where('parent_id', null)
    ->where('id', '!=', 62) // استثني Others نفسه
    ->max('sort_order');

if ($maxSectionOrder) {
    DB::table('categories')
        ->where('id', 62) // Others section
        ->update([
            'sort_order' => $maxSectionOrder + 1,
            'updated_at' => $timestamp
        ]);
    $this->command->info("  ✅ Moved 'Others' section to sort_order: " . ($maxSectionOrder + 1));
} else {
    // إذا مش لاقي، ضعه في 99 (رقم كبير)
    DB::table('categories')
        ->where('id', 62)
        ->update([
            'sort_order' => 99,
            'updated_at' => $timestamp
        ]);
    $this->command->info("  ✅ Set 'Others' section to sort_order: 99");
}

        // ========================================
        // STEP 4: Safe Delete Categories
        // ========================================
        $this->command->info('🗑️  Deleting unused categories (checking for user associations)...');

        $categoriesToDelete = [
            // Home & Property deletions
            16, // Roofing Contractors
            18, // Painting Services
            28, // Snow Removal
            70, // Flooring Installation & Repair
            71, // Repairs And Maintenance
            73, // Fencing Installation & Repair
            74, // Junk Removal
            75, // Water Damage Restoration
            76, // Garage Door Installation

            // Personal & Lifestyle deletions
            39, // Restaurants & Catering
            84, // Tattoo & Piercing Artists
            85, // Pet Grooming

            // Professional & Business deletions
            78, // HR & Recruiting
            79, // IT Support
            81, // Graphic Design
            83, // Printing Services

            // Technical & Repair deletions
            51, // AC & Refrigeration
            52, // Generator Repair
            87, // TV & Streaming Services

            // Automotive deletions
            8,  // Oil Change Services
            12, // Cars Inspections (Safety) for Uber
            65, // Lockout Service

            // Event & Entertainment - Individual deletions (will delete section later)
            54, // Photographers (merged into ID 96)
            55, // Videographers (merged into ID 96)
            56, // DJs & Music
            57, // Catering Services (event)
            58, // Decorators
            59, // Event Planners
            60, // Entertainers
        ];

        $deletedCount = 0;
        $skippedCount = 0;

foreach ($categoriesToDelete as $id) {
    $category = DB::table('categories')->where('id', $id)->first();

    if (!$category) {
        $this->command->warn("  ⚠️  Category ID {$id} not found");
        continue;
    }

    // تحقق فقط من service_providers
    $userCount = DB::table('service_providers')->where('category_id', $id)->count();

    if ($userCount > 0) {
        $this->command->error("  ❌ CANNOT DELETE ID {$id} ({$category->name}) - {$userCount} service providers!");
        $this->command->info("     → Run: php artisan db:seed --class=MigrateUsersSeeder");
        $skippedCount++;
        continue;
    }

    DB::table('categories')->where('id', $id)->delete();
    $this->command->info("  ✅ Deleted: {$category->name} (ID: {$id})");
    $deletedCount++;
}

        $this->command->newLine();

        // ========================================
        // STEP 5: Delete Event & Entertainment Section
        // ========================================
        $this->command->info('🗑️  Deleting Event & Entertainment Services section...');

        $eventSection = DB::table('categories')->where('id', 6)->first();

        if ($eventSection) {
            // Check for any remaining subcategories
            $remainingSubcats = DB::table('categories')->where('parent_id', 6)->count();

            if ($remainingSubcats > 0) {
                $this->command->error("  ❌ Cannot delete Event & Entertainment section - {$remainingSubcats} subcategories still exist!");
                $this->command->error("     → Some categories may have user data. Check manually.");
            } else {
                DB::table('categories')->where('id', 6)->delete();
                $this->command->info('  ✅ Deleted: Event & Entertainment Services section');
            }
        } else {
            $this->command->warn('  ⚠️  Event & Entertainment section already deleted');
        }

        $this->command->newLine();

        // ========================================
        // STEP 6: Update AUTO_INCREMENT
        // ========================================
        DB::statement('ALTER TABLE categories AUTO_INCREMENT = 98');
        $this->command->info('🔢 Updated AUTO_INCREMENT to 98');

        // ========================================
        // Summary
        // ========================================
        $this->command->newLine();
        $this->command->info('════════════════════════════════════════');
        $this->command->info('✨ Category Update Summary:');
        $this->command->info("   • New sections: 2 (Food Services, Construction Services)");
        $this->command->info("   • New categories: 5");
        $this->command->info("   • Updated: 2 (renamed + moved)");
        $this->command->info("   • Deleted: {$deletedCount}");
        $this->command->info("   • Skipped (has data): {$skippedCount}");
        $this->command->info('════════════════════════════════════════');

        if ($skippedCount > 0) {
            $this->command->newLine();
            $this->command->warn('⚠️  WARNING: Some categories could not be deleted due to existing data.');
            $this->command->warn('   Please migrate users/listings from these categories before deletion.');
        }
    }
}
