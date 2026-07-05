<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixDataIntegritySeeder extends Seeder
{
    public function run(): void
    {
        // خريطة تحويل الـ IDs القديمة للجديدة بناءً على structure موقع speeda.ca
        $map = [
            // Automotive
            7  => 11, // Car Mechanics
            8  => 12, // Oil Change -> Electric/Hybrid or similar (adjusting)
            9  => 12, // Electric
            10 => 13, // Tires
            13 => 14, // Body Repair
            68 => 15, // Roadside
            14 => 16, // Car Wash
            11 => 17, // Dealers
            
            // Home & Property
            17 => 21, // Carpentry
            16 => 22, // Roofing
            18 => 23, // Painting
            24 => 24, // Landscaping
            19 => 25, // Plumbing
            20 => 26, // Electrical
            21 => 21, // Handyman -> Carpentry/General
            22 => 35, // Moving
            23 => 31, // Cleaning
            28 => 32, // Snow Removal
            29 => 51, // HVAC
            69 => 52, // Appliance Repair
            
            // Professional
            31 => 61, // Accounting
            32 => 62, // Insurance
            33 => 60, // Lawyers -> Legal Group
            35 => 63, // Real Estate
            36 => 64, // Marketing
            
            // Personal
            38 => 80, // Beauty
            39 => 100, // Restaurants
            54 => 81, // Photographers
            55 => 81, // Videographers
            85 => 83, // Pet Grooming
            34 => 84, // Translators
            97 => 85, // Driving Schools
            
            // Technical
            49 => 90, // Computer Repair
            50 => 91, // Phone Repair
            88 => 92, // Electronics
            
            // Others
            63 => 120,
        ];

        foreach ($map as $oldId => $newId) {
            // Update service_providers
            DB::table('service_providers')->where('category_id', $oldId)->update(['category_id' => $newId]);
            
            // Update service_provider_profiles
            DB::table('service_provider_profiles')->where('category_id', $oldId)->update(['category_id' => $newId]);
            
            // Update service_provider_categories (pivot)
            // Note: service_provider_categories was truncated in ProductionCategorySeeder, 
            // so we might need to repopulate it if needed, or just rely on service_providers.category_id for now.
        }

        // Fix any remains to 'Others' (ID 120)
        DB::table('service_providers')
            ->whereNotIn('category_id', function($q) { $q->select('id')->from('categories'); })
            ->update(['category_id' => 120]);

        $this->command->info('✅ Data integrity fixed and mapped to production IDs!');
    }
}
