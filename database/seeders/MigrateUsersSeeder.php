<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateUsersSeeder extends Seeder
{
    /**
     * هذا الـ Seeder لنقل المستخدمين من الفئات القديمة إلى فئات بديلة
     * استخدمه فقط إذا واجهت مستخدمين في الفئات المحذوفة
     */
    public function run(): void
    {
        $this->command->info('🔄 Starting user migration...');
        
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

        $migratedCount = 0;
        $totalUsers = 0;

        foreach ($migrationMap as $oldCategoryId => $newCategoryId) {
            // عد المستخدمين في الفئة القديمة
            $users = DB::table('service_providers')
                ->where('category_id', $oldCategoryId)
                ->get(['id', 'user_id']);
            
            $userCount = $users->count();
            
            if ($userCount > 0) {
                $totalUsers += $userCount;
                
                // الحصول على أسماء الفئات
                $oldCategory = DB::table('categories')->where('id', $oldCategoryId)->value('name');
                $newCategory = DB::table('categories')->where('id', $newCategoryId)->value('name');
                
                $this->command->warn("📦 Found {$userCount} users in: {$oldCategory} (ID: {$oldCategoryId})");
                $this->command->info("   → Moving to: {$newCategory} (ID: {$newCategoryId})");
                
                // نقل المستخدمين
                DB::table('service_providers')
                    ->where('category_id', $oldCategoryId)
                    ->update([
                        'category_id' => $newCategoryId,
                        'updated_at' => now()
                    ]);
                
                $migratedCount += $userCount;
                
                // عرض تفاصيل المستخدمين المنقولين
                foreach ($users as $user) {
                    $userName = DB::table('users')->where('id', $user->user_id)->value('name');
                    $this->command->line("      ✓ User: {$userName} (ID: {$user->user_id})");
                }
            }
        }

        $this->command->newLine();
        $this->command->info('════════════════════════════════════════');
        $this->command->info("✅ Migration Complete!");
        $this->command->info("   • Total users found: {$totalUsers}");
        $this->command->info("   • Successfully migrated: {$migratedCount}");
        $this->command->info('════════════════════════════════════════');
        
        if ($migratedCount > 0) {
            $this->command->newLine();
            $this->command->warn('⚠️  IMPORTANT: Now run SafeCategoryUpdateSeeder to complete the update.');
        }
    }
}
