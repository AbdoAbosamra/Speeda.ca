<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductionCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign keys and truncate
        Schema::disableForeignKeyConstraints();
        DB::table('categories')->truncate();
        DB::table('service_provider_categories')->truncate();
        Schema::enableForeignKeyConstraints();

        $timestamp = now();

        // 1. Sections
        $sections = [
            ['id' => 1, 'name' => 'Automotive Services', 'name_ar' => 'خدمات السيارات', 'slug' => 'automotive-services', 'icon' => 'fas fa-car', 'color' => '#dc3545', 'sort_order' => 1],
            ['id' => 2, 'name' => 'Home & Property Services', 'name_ar' => 'خدمات المنزل والعقارات', 'slug' => 'home-property-services', 'icon' => 'fas fa-home', 'color' => '#28a745', 'sort_order' => 2],
            ['id' => 3, 'name' => 'Professional & Business Services', 'name_ar' => 'خدمات احترافية وتجارية', 'slug' => 'professional-business-services', 'icon' => 'fas fa-briefcase', 'color' => '#007bff', 'sort_order' => 3],
            ['id' => 4, 'name' => 'Personal & Lifestyle Services', 'name_ar' => 'خدمات شخصية ونمط حياة', 'slug' => 'personal-lifestyle-services', 'icon' => 'fas fa-heart', 'color' => '#fd7e14', 'sort_order' => 4],
            ['id' => 5, 'name' => 'Technical & Repair Services', 'name_ar' => 'خدمات تقنية وإصلاح', 'slug' => 'technical-repair-services', 'icon' => 'fas fa-tools', 'color' => '#6f42c1', 'sort_order' => 5],
            ['id' => 6, 'name' => 'Food Services', 'name_ar' => 'خدمات الطعام', 'slug' => 'food-services', 'icon' => 'fas fa-utensils', 'color' => '#ff6b6b', 'sort_order' => 6],
            ['id' => 7, 'name' => 'Grocery & Supermarkets', 'name_ar' => 'البقالة والسوبر ماركت', 'slug' => 'grocery-supermarkets', 'icon' => 'fas fa-shopping-basket', 'color' => '#20c997', 'sort_order' => 7],
            ['id' => 8, 'name' => 'Others', 'name_ar' => 'أخرى', 'slug' => 'others', 'icon' => 'fas fa-ellipsis-h', 'color' => '#6c757d', 'sort_order' => 8],
        ];

        foreach ($sections as $s) {
            DB::table('categories')->insert(array_merge($s, [
                'is_section' => 1,
                'is_active' => 1,
                'parent_id' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]));
        }

        // 2. Automotive Subcategories & Groups
        $autoGroups = [
            ['id' => 10, 'name' => 'Car Repair & Maintenance', 'slug' => 'car-repair-maintenance', 'parent_id' => 1, 'sort_order' => 1],
        ];
        foreach ($autoGroups as $g) {
            DB::table('categories')->insert(array_merge($g, ['is_section' => 0, 'is_active' => 1, 'created_at' => $timestamp, 'updated_at' => $timestamp]));
        }

        $autoSub = [
            ['id' => 11, 'name' => 'Car Mechanics', 'slug' => 'car-mechanics', 'parent_id' => 10, 'sort_order' => 1],
            ['id' => 12, 'name' => 'Electric & Hybrid Car Service', 'slug' => 'electric-hybrid-car-service', 'parent_id' => 10, 'sort_order' => 2],
            ['id' => 13, 'name' => 'Tire Balancing & Wheel Alignment', 'slug' => 'tire-balancing-wheel-alignment', 'parent_id' => 10, 'sort_order' => 3],
            ['id' => 14, 'name' => 'Auto Body Repair', 'slug' => 'auto-body-repair', 'parent_id' => 10, 'sort_order' => 4],
            ['id' => 15, 'name' => 'Roadside Assistance (24/7)', 'slug' => 'roadside-assistance-24-7', 'parent_id' => 1, 'sort_order' => 2],
            ['id' => 16, 'name' => 'Car Wash & Detailing', 'slug' => 'car-wash-detailing', 'parent_id' => 1, 'sort_order' => 3],
            ['id' => 17, 'name' => 'Car Dealers', 'slug' => 'car-dealers', 'parent_id' => 1, 'sort_order' => 4],
        ];
        foreach ($autoSub as $s) {
            DB::table('categories')->insert(array_merge($s, ['is_section' => 0, 'is_active' => 1, 'created_at' => $timestamp, 'updated_at' => $timestamp]));
        }

        // 3. Home & Property
        $homeGroups = [
            ['id' => 20, 'name' => 'Home Repair & Maintenance', 'slug' => 'home-repair-maintenance', 'parent_id' => 2, 'sort_order' => 1],
            ['id' => 30, 'name' => 'Cleaning & Outdoor', 'slug' => 'cleaning-outdoor', 'parent_id' => 2, 'sort_order' => 2],
            ['id' => 40, 'name' => 'Renovation & Construction', 'slug' => 'renovation-construction', 'parent_id' => 2, 'sort_order' => 4],
            ['id' => 50, 'name' => 'Specialized Services', 'slug' => 'specialized-services-home', 'parent_id' => 2, 'sort_order' => 5],
        ];
        foreach ($homeGroups as $g) {
            DB::table('categories')->insert(array_merge($g, ['is_section' => 0, 'is_active' => 1, 'created_at' => $timestamp, 'updated_at' => $timestamp]));
        }

        $homeSub = [
            ['id' => 21, 'name' => 'Carpentry', 'slug' => 'carpentry', 'parent_id' => 20, 'sort_order' => 1],
            ['id' => 22, 'name' => 'Roofing', 'slug' => 'roofing', 'parent_id' => 20, 'sort_order' => 2],
            ['id' => 23, 'name' => 'Painting', 'slug' => 'painting', 'parent_id' => 20, 'sort_order' => 3],
            ['id' => 24, 'name' => 'Landscaping', 'slug' => 'landscaping', 'parent_id' => 20, 'sort_order' => 4],
            ['id' => 25, 'name' => 'Plumbing', 'slug' => 'plumbing', 'parent_id' => 20, 'sort_order' => 5],
            ['id' => 26, 'name' => 'Electrical Services', 'slug' => 'electrical-services', 'parent_id' => 20, 'sort_order' => 6],
            ['id' => 27, 'name' => 'Locksmith Services', 'slug' => 'locksmith-services', 'parent_id' => 20, 'sort_order' => 7],

            ['id' => 31, 'name' => 'House Cleaning', 'slug' => 'house-cleaning', 'parent_id' => 30, 'sort_order' => 1],
            ['id' => 32, 'name' => 'Snow Removal', 'slug' => 'snow-removal', 'parent_id' => 30, 'sort_order' => 2],

            ['id' => 35, 'name' => 'Moving Services', 'slug' => 'moving-services', 'parent_id' => 2, 'sort_order' => 3],

            ['id' => 41, 'name' => 'Home Renovation', 'slug' => 'home-renovation', 'parent_id' => 40, 'sort_order' => 1],
            ['id' => 42, 'name' => 'General Construction', 'slug' => 'general-construction', 'parent_id' => 40, 'sort_order' => 2],

            ['id' => 51, 'name' => 'HVAC Services', 'slug' => 'hvac-services', 'parent_id' => 50, 'sort_order' => 1],
            ['id' => 52, 'name' => 'Appliance Repair', 'slug' => 'appliance-repair', 'parent_id' => 50, 'sort_order' => 2],
        ];
        foreach ($homeSub as $s) {
            DB::table('categories')->insert(array_merge($s, ['is_section' => 0, 'is_active' => 1, 'created_at' => $timestamp, 'updated_at' => $timestamp]));
        }

        // 4. Professional & Business
        $profGroups = [
            ['id' => 60, 'name' => 'Legal Services', 'slug' => 'legal-services', 'parent_id' => 3, 'sort_order' => 3],
            ['id' => 70, 'name' => 'Marketing & Web Services', 'slug' => 'marketing-web-services', 'parent_id' => 3, 'sort_order' => 7],
        ];
        foreach ($profGroups as $g) {
            DB::table('categories')->insert(array_merge($g, ['is_section' => 0, 'is_active' => 1, 'created_at' => $timestamp, 'updated_at' => $timestamp]));
        }

        $profSub = [
            ['id' => 61, 'name' => 'Accounting, Bookkeeping & Tax Preparation', 'slug' => 'accounting-bookkeeping-tax-preparation', 'parent_id' => 3, 'sort_order' => 1],
            ['id' => 62, 'name' => 'Insurance Brokers', 'slug' => 'insurance-brokers', 'parent_id' => 3, 'sort_order' => 2],
            ['id' => 63, 'name' => 'Real Estate Services', 'slug' => 'real-estate-services', 'parent_id' => 3, 'sort_order' => 4],
            ['id' => 64, 'name' => 'Marketing & Advertising', 'slug' => 'marketing-advertising', 'parent_id' => 3, 'sort_order' => 5],

            ['id' => 71, 'name' => 'SEO & Digital Marketing', 'slug' => 'seo-digital-marketing', 'parent_id' => 70, 'sort_order' => 1],
            ['id' => 72, 'name' => 'Web Development', 'slug' => 'web-development', 'parent_id' => 70, 'sort_order' => 2],
            ['id' => 73, 'name' => 'Graphic Design', 'slug' => 'graphic-design', 'parent_id' => 70, 'sort_order' => 3],
        ];
        foreach ($profSub as $s) {
            DB::table('categories')->insert(array_merge($s, ['is_section' => 0, 'is_active' => 1, 'created_at' => $timestamp, 'updated_at' => $timestamp]));
        }

        // 5. Personal & Lifestyle
        $lifeSub = [
            ['id' => 80, 'name' => 'Beauty & Grooming', 'slug' => 'beauty-grooming', 'parent_id' => 4, 'sort_order' => 1],
            ['id' => 81, 'name' => 'Photography & Videography', 'slug' => 'photography-videography', 'parent_id' => 4, 'sort_order' => 4],
            ['id' => 82, 'name' => 'Event Planning & Services', 'slug' => 'event-planning-services', 'parent_id' => 4, 'sort_order' => 6],
            ['id' => 83, 'name' => 'Pet Services', 'slug' => 'pet-services', 'parent_id' => 4, 'sort_order' => 7],
            ['id' => 84, 'name' => 'Translators & Interpreters', 'slug' => 'translators-interpreters', 'parent_id' => 4, 'sort_order' => 10],
            ['id' => 85, 'name' => 'Driving Lessons & Schools', 'slug' => 'driving-lessons-schools', 'parent_id' => 4, 'sort_order' => 11],
        ];
        foreach ($lifeSub as $s) {
            DB::table('categories')->insert(array_merge($s, ['is_section' => 0, 'is_active' => 1, 'created_at' => $timestamp, 'updated_at' => $timestamp]));
        }

        // 6. Technical
        $techSub = [
            ['id' => 90, 'name' => 'Computer Repair', 'slug' => 'computer-repair', 'parent_id' => 5, 'sort_order' => 1],
            ['id' => 91, 'name' => 'Phone Repair', 'slug' => 'phone-repair', 'parent_id' => 5, 'sort_order' => 2],
            ['id' => 92, 'name' => 'Electronics Repair & Maintenance', 'slug' => 'electronics-repair-maintenance', 'parent_id' => 5, 'sort_order' => 3],
        ];
        foreach ($techSub as $s) {
            DB::table('categories')->insert(array_merge($s, ['is_section' => 0, 'is_active' => 1, 'created_at' => $timestamp, 'updated_at' => $timestamp]));
        }

        // 7. Food Services
        $foodSub = [
            ['id' => 100, 'name' => 'Restaurants & Cafe', 'slug' => 'restaurants-cafe', 'parent_id' => 6, 'sort_order' => 1],
            ['id' => 101, 'name' => 'Home Kitchen', 'slug' => 'home-kitchen', 'parent_id' => 6, 'sort_order' => 2],
            ['id' => 102, 'name' => 'Catering Services', 'slug' => 'catering-services-food', 'parent_id' => 6, 'sort_order' => 3],
        ];
        foreach ($foodSub as $s) {
            DB::table('categories')->insert(array_merge($s, ['is_section' => 0, 'is_active' => 1, 'created_at' => $timestamp, 'updated_at' => $timestamp]));
        }

        // 8. Grocery
        DB::table('categories')->insert([
            'id' => 110, 'name' => 'Grocery & Supermarkets', 'slug' => 'grocery-supermarkets-cat', 'parent_id' => 7, 'is_section' => 0, 'is_active' => 1, 'sort_order' => 1, 'created_at' => $timestamp, 'updated_at' => $timestamp,
        ]);

        // 9. Others
        DB::table('categories')->insert([
            'id' => 120, 'name' => 'Others', 'slug' => 'others-cat', 'parent_id' => 8, 'is_section' => 0, 'is_active' => 1, 'sort_order' => 1, 'created_at' => $timestamp, 'updated_at' => $timestamp,
        ]);

        // Rebuilding the taxonomy above changes category IDs, which would orphan
        // any provider still pointing at an old ID (rendered as "Uncategorized").
        // Reassign every provider whose category_id no longer exists to the
        // "Others" catch-all so no provider is ever left uncategorized.
        $validCategoryIds = DB::table('categories')->pluck('id')->all();
        $orphaned = DB::table('service_providers')
            ->where(function ($q) use ($validCategoryIds) {
                $q->whereNull('category_id')
                    ->orWhereNotIn('category_id', $validCategoryIds);
            })
            ->update(['category_id' => 120, 'updated_at' => $timestamp]);

        if ($orphaned > 0) {
            $this->command->warn("🔧 Reassigned {$orphaned} orphaned provider(s) to 'Others'.");
        }

        $this->command->info('✅ Categories synced with production structure!');
    }
}
