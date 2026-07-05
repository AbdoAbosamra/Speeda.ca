<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        DB::transaction(function () {
            $this->syncSections();
            $this->syncAutomotive();
            $this->syncHomeAndProperty();
            $this->syncProfessional();
            $this->syncPersonal();
            $this->syncTechnical();
            $this->syncFood();
            $this->syncGrocery();
            $this->retireLegacyConstructionSection();
        });
    }

    public function down(): void
    {
        // Production-safe forward-only taxonomy migration.
    }

    private function syncSections(): void
    {
        $this->updateCategory(1, [
            'name' => 'Automotive Services',
            'name_en' => 'Automotive Services',
            'name_ar' => 'خدمات السيارات',
            'name_fr' => 'Services automobiles',
            'slug' => 'automotive-services',
            'icon' => 'fas fa-car',
            'sort_order' => 1,
            'is_section' => true,
            'is_active' => true,
            'parent_id' => null,
            'deleted_at' => null,
        ]);

        $this->updateCategory(2, [
            'name' => 'Home & Property Services',
            'name_en' => 'Home & Property Services',
            'name_ar' => 'خدمات المنزل والممتلكات',
            'name_fr' => 'Services de maison et propriété',
            'slug' => 'home-property-services',
            'icon' => 'fas fa-home',
            'sort_order' => 2,
            'is_section' => true,
            'is_active' => true,
            'parent_id' => null,
            'deleted_at' => null,
        ]);

        $this->updateCategory(3, [
            'name' => 'Professional & Business Services',
            'name_en' => 'Professional & Business Services',
            'name_ar' => 'الخدمات المهنية والتجارية',
            'name_fr' => 'Services professionnels et commerciaux',
            'slug' => 'professional-business-services',
            'icon' => 'fas fa-briefcase',
            'sort_order' => 3,
            'is_section' => true,
            'is_active' => true,
            'parent_id' => null,
            'deleted_at' => null,
        ]);

        $this->updateCategory(4, [
            'name' => 'Personal & Lifestyle Services',
            'name_en' => 'Personal & Lifestyle Services',
            'name_ar' => 'الخدمات الشخصية ونمط الحياة',
            'name_fr' => 'Services personnels et style de vie',
            'slug' => 'personal-lifestyle-services',
            'icon' => 'fas fa-heart',
            'sort_order' => 4,
            'is_section' => true,
            'is_active' => true,
            'parent_id' => null,
            'deleted_at' => null,
        ]);

        $this->updateCategory(5, [
            'name' => 'Technical & Repair Services',
            'name_en' => 'Technical & Repair Services',
            'name_ar' => 'الخدمات التقنية والإصلاح',
            'name_fr' => 'Services techniques et réparation',
            'slug' => 'technical-repair-services',
            'icon' => 'fas fa-tools',
            'sort_order' => 5,
            'is_section' => true,
            'is_active' => true,
            'parent_id' => null,
            'deleted_at' => null,
        ]);

        $this->updateCategory(90, [
            'name' => 'Food Services',
            'name_en' => 'Food Services',
            'name_ar' => 'خدمات الطعام',
            'name_fr' => 'Services alimentaires',
            'slug' => 'food-services',
            'icon' => 'fas fa-utensils',
            'sort_order' => 6,
            'is_section' => true,
            'is_active' => true,
            'parent_id' => null,
            'deleted_at' => null,
        ]);

        $this->ensureCategory([
            'name' => 'Grocery & Supermarkets',
            'name_en' => 'Grocery & Supermarkets',
            'name_ar' => 'البقالة والسوبر ماركت',
            'name_fr' => 'Épiceries et supermarchés',
            'slug' => 'grocery-supermarkets',
            'icon' => 'fas fa-store',
            'sort_order' => 7,
            'is_section' => true,
            'is_active' => true,
            'parent_id' => null,
            'deleted_at' => null,
        ], ['slug' => 'grocery-supermarkets']);

        // Keep the legacy "Others" branch available for existing business rules,
        // but push it out of the public taxonomy ordering.
        if (DB::table('categories')->where('id', 62)->exists()) {
            $this->updateCategory(62, [
                'sort_order' => 99,
                'is_section' => true,
                'parent_id' => null,
                'is_active' => true,
            ]);
        }
    }

    private function syncAutomotive(): void
    {
        $groupId = $this->ensureCategory([
            'name' => 'Car Repair & Maintenance',
            'name_en' => 'Car Repair & Maintenance',
            'name_ar' => 'إصلاح وصيانة السيارات',
            'name_fr' => 'Réparation et entretien automobile',
            'slug' => 'car-repair-maintenance',
            'icon' => 'fas fa-wrench',
            'sort_order' => 1,
            'is_section' => false,
            'is_active' => true,
            'parent_id' => 1,
            'deleted_at' => null,
        ], ['slug' => 'car-repair-maintenance']);

        $this->updateCategory(7, [
            'name' => 'Car Mechanics',
            'name_en' => 'Car Mechanics',
            'name_ar' => 'ميكانيكا السيارات',
            'name_fr' => 'Mécaniciens automobiles',
            'parent_id' => $groupId,
            'sort_order' => 1,
            'deleted_at' => null,
        ]);

        $this->updateCategory(9, [
            'name' => 'Electric & Hybrid Car Service',
            'name_en' => 'Electric & Hybrid Car Service',
            'name_ar' => 'خدمة السيارات الكهربائية والهجينة',
            'name_fr' => 'Service de voitures électriques et hybrides',
            'parent_id' => $groupId,
            'sort_order' => 2,
            'deleted_at' => null,
        ]);

        $this->updateCategory(10, [
            'name' => 'Tire Balancing & Wheel Alignment',
            'name_en' => 'Tire Balancing & Wheel Alignment',
            'name_ar' => 'ترصيص الإطارات وضبط زوايا العجلات',
            'name_fr' => 'Équilibrage des pneus et alignement des roues',
            'parent_id' => $groupId,
            'sort_order' => 3,
            'deleted_at' => null,
        ]);

        $this->updateCategory(13, [
            'name' => 'Auto Body Repair',
            'name_en' => 'Auto Body Repair',
            'name_ar' => 'إصلاح هيكل السيارة',
            'name_fr' => 'Réparation de carrosserie',
            'parent_id' => $groupId,
            'sort_order' => 4,
            'deleted_at' => null,
        ]);

        $this->updateCategory(68, [
            'parent_id' => 1,
            'sort_order' => 2,
            'deleted_at' => null,
        ]);

        $this->updateCategory(14, [
            'parent_id' => 1,
            'sort_order' => 3,
            'deleted_at' => null,
        ]);

        $this->updateCategory(11, [
            'parent_id' => 1,
            'sort_order' => 4,
            'deleted_at' => null,
        ]);
    }

    private function syncHomeAndProperty(): void
    {
        $homeRepairId = $this->ensureCategory([
            'name' => 'Home Repair & Maintenance',
            'name_en' => 'Home Repair & Maintenance',
            'name_ar' => 'إصلاح وصيانة المنازل',
            'name_fr' => 'Réparation et entretien de la maison',
            'slug' => 'home-repair-maintenance',
            'icon' => 'fas fa-house-user',
            'sort_order' => 1,
            'is_section' => false,
            'is_active' => true,
            'parent_id' => 2,
            'deleted_at' => null,
        ], ['slug' => 'home-repair-maintenance']);

        $cleaningOutdoorId = $this->ensureCategory([
            'name' => 'Cleaning & Outdoor',
            'name_en' => 'Cleaning & Outdoor',
            'name_ar' => 'التنظيف والخدمات الخارجية',
            'name_fr' => 'Nettoyage et extérieur',
            'slug' => 'cleaning-outdoor',
            'icon' => 'fas fa-seedling',
            'sort_order' => 2,
            'is_section' => false,
            'is_active' => true,
            'parent_id' => 2,
            'deleted_at' => null,
        ], ['slug' => 'cleaning-outdoor']);

        $renovationId = $this->ensureCategory([
            'name' => 'Renovation & Construction',
            'name_en' => 'Renovation & Construction',
            'name_ar' => 'التجديد والإنشاءات',
            'name_fr' => 'Rénovation et construction',
            'slug' => 'renovation-construction',
            'icon' => 'fas fa-hard-hat',
            'sort_order' => 5,
            'is_section' => false,
            'is_active' => true,
            'parent_id' => 2,
            'deleted_at' => null,
        ], ['slug' => 'renovation-construction']);

        $specializedId = $this->ensureCategory([
            'name' => 'Specialized Services',
            'name_en' => 'Specialized Services',
            'name_ar' => 'الخدمات المتخصصة',
            'name_fr' => 'Services spécialisés',
            'slug' => 'specialized-services',
            'icon' => 'fas fa-shield-virus',
            'sort_order' => 6,
            'is_section' => false,
            'is_active' => true,
            'parent_id' => 2,
            'deleted_at' => null,
        ], ['slug' => 'specialized-services']);

        $this->updateCategory(19, [
            'name' => 'Plumbing',
            'name_en' => 'Plumbing',
            'name_ar' => 'السباكة',
            'name_fr' => 'Plomberie',
            'parent_id' => $homeRepairId,
            'sort_order' => 1,
            'deleted_at' => null,
        ]);

        $this->updateCategory(20, [
            'name' => 'Electrical',
            'name_en' => 'Electrical',
            'name_ar' => 'الكهرباء',
            'name_fr' => 'Électricité',
            'parent_id' => $homeRepairId,
            'sort_order' => 2,
            'deleted_at' => null,
        ]);

        $this->updateCategory(21, [
            'name' => 'Handyman',
            'name_en' => 'Handyman',
            'name_ar' => 'عامل الصيانة',
            'name_fr' => 'Homme à tout faire',
            'parent_id' => $homeRepairId,
            'sort_order' => 3,
            'deleted_at' => null,
        ]);

        $this->updateCategory(69, [
            'name' => 'Appliance Repair',
            'name_en' => 'Appliance Repair',
            'name_ar' => 'إصلاح الأجهزة المنزلية',
            'name_fr' => 'Réparation d’appareils ménagers',
            'parent_id' => $homeRepairId,
            'sort_order' => 4,
            'deleted_at' => null,
        ]);

        $this->updateCategory(29, [
            'name' => 'HVAC',
            'name_en' => 'HVAC',
            'name_ar' => 'التدفئة والتهوية والتكييف',
            'name_fr' => 'CVCA',
            'parent_id' => $homeRepairId,
            'sort_order' => 5,
            'deleted_at' => null,
        ]);

        $this->updateCategory(23, [
            'parent_id' => $cleaningOutdoorId,
            'sort_order' => 1,
            'deleted_at' => null,
        ]);

        // Keep the existing provider-linked landscaping category under
        // Renovation & Construction so grouped filtering includes it there.
        $this->updateCategory(24, [
            'parent_id' => $renovationId,
            'sort_order' => 4,
            'deleted_at' => null,
        ]);

        $this->ensureCategory([
            'name' => 'Landscaping & Gardening',
            'name_en' => 'Landscaping & Gardening',
            'name_ar' => 'تنسيق الحدائق والبستنة',
            'name_fr' => 'Aménagement paysager et jardinage',
            'slug' => 'landscaping-gardening-outdoor',
            'icon' => 'fas fa-leaf',
            'sort_order' => 2,
            'is_section' => false,
            'is_active' => true,
            'parent_id' => $cleaningOutdoorId,
            'deleted_at' => null,
        ], ['slug' => 'landscaping-gardening-outdoor']);

        $this->updateCategory(22, [
            'parent_id' => 2,
            'sort_order' => 3,
            'deleted_at' => null,
        ]);

        $this->updateCategory(27, [
            'parent_id' => 2,
            'sort_order' => 4,
            'deleted_at' => null,
        ]);

        $this->updateCategory(25, [
            'parent_id' => $renovationId,
            'sort_order' => 1,
            'deleted_at' => null,
        ]);

        $this->updateCategory(17, [
            'parent_id' => $renovationId,
            'sort_order' => 2,
            'deleted_at' => null,
        ]);

        $this->updateCategory(95, [
            'name' => 'General Construction',
            'name_en' => 'General Construction',
            'name_ar' => 'الإنشاءات العامة',
            'name_fr' => 'Construction générale',
            'parent_id' => $renovationId,
            'sort_order' => 3,
            'deleted_at' => null,
        ]);

        $this->updateCategory(26, [
            'parent_id' => $specializedId,
            'sort_order' => 1,
            'deleted_at' => null,
        ]);
    }

    private function syncProfessional(): void
    {
        $legalId = $this->ensureCategory([
            'name' => 'Legal Services',
            'name_en' => 'Legal Services',
            'name_ar' => 'الخدمات القانونية',
            'name_fr' => 'Services juridiques',
            'slug' => 'legal-services',
            'icon' => 'fas fa-balance-scale',
            'sort_order' => 3,
            'is_section' => false,
            'is_active' => true,
            'parent_id' => 3,
            'deleted_at' => null,
        ], ['slug' => 'legal-services']);

        $marketingId = $this->ensureCategory([
            'name' => 'Marketing & Web Services',
            'name_en' => 'Marketing & Web Services',
            'name_ar' => 'خدمات التسويق والويب',
            'name_fr' => 'Services marketing et web',
            'slug' => 'marketing-web-services',
            'icon' => 'fas fa-bullhorn',
            'sort_order' => 5,
            'is_section' => false,
            'is_active' => true,
            'parent_id' => 3,
            'deleted_at' => null,
        ], ['slug' => 'marketing-web-services']);

        $this->updateCategory(31, [
            'name' => 'Accounting, Bookkeeping & Tax Preparation',
            'name_en' => 'Accounting, Bookkeeping & Tax Preparation',
            'name_ar' => 'المحاسبة ومسك الدفاتر وإعداد الضرائب',
            'name_fr' => 'Comptabilité, tenue de livres et préparation fiscale',
            'parent_id' => 3,
            'sort_order' => 1,
            'deleted_at' => null,
        ]);

        $this->updateCategory(32, [
            'parent_id' => 3,
            'sort_order' => 2,
            'deleted_at' => null,
        ]);

        $this->updateCategory(33, [
            'parent_id' => $legalId,
            'sort_order' => 1,
            'deleted_at' => null,
        ]);

        $this->updateCategory(82, [
            'parent_id' => $legalId,
            'sort_order' => 2,
            'deleted_at' => null,
        ]);

        $this->updateCategory(35, [
            'parent_id' => 3,
            'sort_order' => 4,
            'deleted_at' => null,
        ]);

        $this->updateCategory(34, [
            'parent_id' => 3,
            'sort_order' => 5,
            'deleted_at' => null,
        ]);

        $this->updateCategory(36, [
            'parent_id' => $marketingId,
            'sort_order' => 1,
            'deleted_at' => null,
        ]);

        $this->updateCategory(80, [
            'parent_id' => $marketingId,
            'sort_order' => 2,
            'deleted_at' => null,
        ]);

        $this->updateCategory(97, [
            'parent_id' => 3,
            'sort_order' => 6,
            'deleted_at' => null,
        ]);
    }

    private function syncPersonal(): void
    {
        $beautyId = $this->ensureCategory([
            'name' => 'Beauty & Grooming',
            'name_en' => 'Beauty & Grooming',
            'name_ar' => 'الجمال والعناية الشخصية',
            'name_fr' => 'Beauté et soins',
            'slug' => 'beauty-grooming',
            'icon' => 'fas fa-spa',
            'sort_order' => 1,
            'is_section' => false,
            'is_active' => true,
            'parent_id' => 4,
            'deleted_at' => null,
        ], ['slug' => 'beauty-grooming']);

        $healthId = $this->ensureCategory([
            'name' => 'Health & Wellness',
            'name_en' => 'Health & Wellness',
            'name_ar' => 'الصحة والعافية',
            'name_fr' => 'Santé et bien-être',
            'slug' => 'health-wellness',
            'icon' => 'fas fa-heartbeat',
            'sort_order' => 2,
            'is_section' => false,
            'is_active' => true,
            'parent_id' => 4,
            'deleted_at' => null,
        ], ['slug' => 'health-wellness']);

        $lifestyleId = $this->ensureCategory([
            'name' => 'Lifestyle Services',
            'name_en' => 'Lifestyle Services',
            'name_ar' => 'خدمات نمط الحياة',
            'name_fr' => 'Services de style de vie',
            'slug' => 'lifestyle-services',
            'icon' => 'fas fa-camera-retro',
            'sort_order' => 3,
            'is_section' => false,
            'is_active' => true,
            'parent_id' => 4,
            'deleted_at' => null,
        ], ['slug' => 'lifestyle-services']);

        $this->updateCategory(43, [
            'parent_id' => $beautyId,
            'sort_order' => 1,
            'deleted_at' => null,
        ]);

        $this->updateCategory(44, [
            'parent_id' => $beautyId,
            'sort_order' => 2,
            'deleted_at' => null,
        ]);

        $this->updateCategory(46, [
            'parent_id' => $beautyId,
            'sort_order' => 3,
            'deleted_at' => null,
        ]);

        $this->updateCategory(38, [
            'parent_id' => $beautyId,
            'sort_order' => 4,
            'deleted_at' => null,
        ]);

        $this->updateCategory(42, [
            'parent_id' => $healthId,
            'sort_order' => 1,
            'deleted_at' => null,
        ]);

        $this->updateCategory(41, [
            'parent_id' => $healthId,
            'sort_order' => 2,
            'deleted_at' => null,
        ]);

        $this->updateCategory(40, [
            'parent_id' => $healthId,
            'sort_order' => 3,
            'deleted_at' => null,
        ]);

        $this->updateCategory(86, [
            'parent_id' => $lifestyleId,
            'sort_order' => 1,
            'deleted_at' => null,
        ]);

        $this->updateCategory(96, [
            'parent_id' => $lifestyleId,
            'sort_order' => 2,
            'deleted_at' => null,
        ]);
    }

    private function syncTechnical(): void
    {
        $this->updateCategory(49, [
            'parent_id' => 5,
            'sort_order' => 1,
            'deleted_at' => null,
        ]);

        $this->updateCategory(50, [
            'parent_id' => 5,
            'sort_order' => 2,
            'deleted_at' => null,
        ]);

        $this->updateCategory(88, [
            'parent_id' => 5,
            'sort_order' => 3,
            'deleted_at' => null,
        ]);
    }

    private function syncFood(): void
    {
        $foodCategoryId = $this->ensureCategory([
            'name' => 'Food Services',
            'name_en' => 'Food Services',
            'name_ar' => 'خدمات الطعام',
            'name_fr' => 'Services alimentaires',
            'slug' => 'food-services-category',
            'icon' => 'fas fa-utensils',
            'sort_order' => 1,
            'is_section' => false,
            'is_active' => true,
            'parent_id' => 90,
            'deleted_at' => null,
        ], ['slug' => 'food-services-category']);

        $this->updateCategory(92, [
            'name' => 'Restaurants & Cafe',
            'name_en' => 'Restaurants & Cafe',
            'name_ar' => 'مطاعم ومقاهٍ',
            'name_fr' => 'Restaurants et cafés',
            'parent_id' => $foodCategoryId,
            'sort_order' => 1,
            'deleted_at' => null,
        ]);

        $this->updateCategory(93, [
            'parent_id' => $foodCategoryId,
            'sort_order' => 2,
            'deleted_at' => null,
        ]);

        $this->updateCategory(94, [
            'parent_id' => $foodCategoryId,
            'sort_order' => 3,
            'deleted_at' => null,
        ]);

        $this->ensureCategory([
            'name' => 'Sweets & Pastries',
            'name_en' => 'Sweets & Pastries',
            'name_ar' => 'حلويات ومعجنات',
            'name_fr' => 'Desserts et pâtisseries',
            'slug' => 'sweets-pastries',
            'icon' => 'fas fa-cookie-bite',
            'sort_order' => 4,
            'is_section' => false,
            'is_active' => true,
            'parent_id' => $foodCategoryId,
            'deleted_at' => null,
        ], ['slug' => 'sweets-pastries']);
    }

    private function syncGrocery(): void
    {
        $section = DB::table('categories')->where('slug', 'grocery-supermarkets')->first();
        if (! $section) {
            return;
        }

        $this->ensureCategory([
            'name' => 'Grocery & Supermarkets',
            'name_en' => 'Grocery & Supermarkets',
            'name_ar' => 'البقالة والسوبر ماركت',
            'name_fr' => 'Épiceries et supermarchés',
            'slug' => 'grocery-supermarkets-category',
            'icon' => 'fas fa-shopping-basket',
            'sort_order' => 1,
            'is_section' => false,
            'is_active' => true,
            'parent_id' => $section->id,
            'deleted_at' => null,
        ], ['slug' => 'grocery-supermarkets-category']);
    }

    private function retireLegacyConstructionSection(): void
    {
        if (DB::table('categories')->where('id', 91)->exists()) {
            $this->updateCategory(91, [
                'is_active' => false,
                'sort_order' => 98,
                'parent_id' => null,
                'is_section' => true,
            ]);
        }
    }

    private function ensureCategory(array $attributes, array $match): int
    {
        $query = DB::table('categories');

        foreach ($match as $column => $value) {
            $query->where($column, $value);
        }

        $existing = $query->first();

        if ($existing) {
            DB::table('categories')->where('id', $existing->id)->update(array_merge(
                $attributes,
                ['updated_at' => now()]
            ));

            return (int) $existing->id;
        }

        return (int) DB::table('categories')->insertGetId(array_merge(
            $attributes,
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ));
    }

    private function updateCategory(int $id, array $attributes): void
    {
        DB::table('categories')
            ->where('id', $id)
            ->update(array_merge($attributes, ['updated_at' => now()]));
    }
};
