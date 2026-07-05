<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateUsersSeeder extends Seeder
{
    /**
     * هذا الـ Seeder لنقل المستخدمين من الفئات القديمة إلى فئات بديلة
     * استخدمه فقط إذا واجهت مستخدمين في الفئات المحذوفة
     */
    public function run(): void
    {
        $this->command->info('🔄 Starting comprehensive category migration...');
        
        // خريطة النقل: [من الفئة القديمة => إلى الفئة الجديدة]
        $migrationMap = [
            // Automotive
            8  => 7,   // Oil Change → Car Mechanics
            12 => 7,   // Cars Inspections → Car Mechanics
            65 => 68,  // Lockout Service → Roadside Assistance
            
            // Home & Property
            16 => 25,  // Roofing → Home Renovation
            18 => 25,  // Painting → Home Renovation
            28 => 63,  // Snow Removal → Others
            70 => 25,  // Flooring → Home Renovation
            71 => 21,  // Repairs → Handyman Services
            73 => 25,  // Fencing → Home Renovation
            74 => 22,  // Junk Removal → Moving Services
            75 => 25,  // Water Damage → Home Renovation
            76 => 25,  // Garage Door → Home Renovation
            
            // Personal & Lifestyle
            39 => 92,  // Restaurants & Catering → Restaurants (new)
            84 => 38,  // Tattoo → Beauty & Personal Care
            85 => 63,  // Pet Grooming → Others
            
            // Professional & Business
            78 => 63,  // HR & Recruiting → Others
            79 => 49,  // IT Support → Computer Repair
            81 => 80,  // Graphic Design → Web Design
            83 => 63,  // Printing → Others
            
            // Technical & Repair
            51 => 29,  // AC & Refrigeration → HVAC Services
            52 => 88,  // Generator → Electronics Repair
            87 => 88,  // TV & Streaming → Electronics Repair
            
            // Event & Entertainment → Personal & Lifestyle
            54 => 96,  // Photographers → Photographers & Videographers (new merged)
            55 => 96,  // Videographers → Photographers & Videographers (new merged)
            56 => 63,  // DJs & Music → Others
            57 => 94,  // Catering Services → Catering Services (Food section)
            58 => 63,  // Decorators → Others
            59 => 63,  // Event Planners → Others
            60 => 63,  // Entertainers → Others
        ];

        $tablesToMigrate = [
            'service_providers' => 'category_id',
            'service_provider_profiles' => 'category_id',
            'service_provider_categories' => 'category_id',
            'location_category' => 'category_id',
        ];

        foreach ($migrationMap as $oldId => $newId) {
            $oldName = DB::table('categories')->where('id', $oldId)->value('name') ?? "Unknown (ID: $oldId)";
            $newName = DB::table('categories')->where('id', $newId)->value('name') ?? "Unknown (ID: $newId)";

            $this->command->warn("📦 Processing Migration: {$oldName} → {$newName}");

            foreach ($tablesToMigrate as $table => $column) {
                if (!Schema::hasTable($table)) continue;

                $count = DB::table($table)->where($column, $oldId)->count();
                
                if ($count > 0) {
                    $this->command->info("   - Table '{$table}': Found {$count} records");

                    // Special handling for pivot/unique tables to avoid duplicate key errors
                    if ($table === 'service_provider_categories') {
                        // Get all provider IDs in the old category
                        $providers = DB::table($table)->where($column, $oldId)->pluck('service_provider_profile_id');
                        
                        foreach ($providers as $profileId) {
                            // Check if they already exist in the new category
                            $exists = DB::table($table)
                                ->where('service_provider_profile_id', $profileId)
                                ->where($column, $newId)
                                ->exists();

                            if ($exists) {
                                // Just delete the old one
                                DB::table($table)
                                    ->where('service_provider_profile_id', $profileId)
                                    ->where($column, $oldId)
                                    ->delete();
                                $this->command->line("      ✓ Removed duplicate pivot for profile #{$profileId}");
                            } else {
                                // Update to new ID
                                DB::table($table)
                                    ->where('service_provider_profile_id', $profileId)
                                    ->where($column, $oldId)
                                    ->update([$column => $newId, 'updated_at' => now()]);
                            }
                        }
                    } 
                    elseif ($table === 'location_category') {
                        // Get all location IDs in the old category
                        $locations = DB::table($table)->where($column, $oldId)->pluck('location_id');
                        
                        foreach ($locations as $locationId) {
                            $exists = DB::table($table)
                                ->where('location_id', $locationId)
                                ->where($column, $newId)
                                ->exists();

                            if ($exists) {
                                DB::table($table)
                                    ->where('location_id', $locationId)
                                    ->where($column, $oldId)
                                    ->delete();
                            } else {
                                DB::table($table)
                                    ->where('location_id', $locationId)
                                    ->where($column, $oldId)
                                    ->update([$column => $newId, 'updated_at' => now()]);
                            }
                        }
                    }
                    else {
                        // Direct update for non-pivot tables
                        DB::table($table)->where($column, $oldId)->update([
                            $column => $newId,
                            'updated_at' => now()
                        ]);
                    }
                    
                    $this->command->line("      ✓ Migrated {$count} records in '{$table}'");
                }
            }
        }

        $this->command->newLine();
        $this->command->info('════════════════════════════════════════');
        $this->command->info("✅ Comprehensive Migration Complete!");
        $this->command->info('════════════════════════════════════════');
        $this->command->newLine();
        $this->command->warn('⚠️  Now run SafeCategoryUpdateSeeder to complete the update.');
    }

}
