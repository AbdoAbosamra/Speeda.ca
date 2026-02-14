<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration populates Arabic and French translations for all categories.
     * It performs a safe update without deleting any existing data.
     */
    public function up(): void
    {
        // Step 1: Ensure all categories have English translations populated
        $this->populateEnglishTranslations();
        
        // Step 2: Add Arabic translations
        $this->populateArabicTranslations();
        
        // Step 3: Add French translations
        $this->populateFrenchTranslations();
        
        \Illuminate\Support\Facades\Log::info('Category translations populated successfully');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For safety, we only log but don't delete translations
        \Illuminate\Support\Facades\Log::warning('Migration down() called - translations kept for safety');
    }

    /**
     * Populate English translations from original name/description fields
     */
    private function populateEnglishTranslations(): void
    {
        DB::table('categories')
            ->whereNull('name_en')
            ->update([
                'name_en' => DB::raw('name'),
            ]);

        DB::table('categories')
            ->whereNull('description_en')
            ->update([
                'description_en' => DB::raw('description'),
            ]);
    }

    /**
     * Populate Arabic translations
     */
    private function populateArabicTranslations(): void
    {
        $arabicTranslations = [
            // Automotive Services
            'Car Maintenance' => 'صيانة السيارات',
            'Oil Changes' => 'تغيير الزيت',
            'Tire Services' => 'خدمات الإطارات',
            'Car Wash' => 'غسيل السيارات',
            'Detailing' => 'تفاصيل السيارة',
            'Engine Diagnostics' => 'تشخيص المحرك',
            'Brake Service' => 'خدمة الفرامل',
            'Battery Replacement' => 'استبدال البطارية',
            'Electrical Repair' => 'إصلاح كهربائي',
            
            // Home & Property Services
            'House Cleaning' => 'تنظيف المنزل',
            'Painting & Decoration' => 'الطلاء والزخرفة',
            'Plumbing' => 'السباكة',
            'Electrical Installation' => 'التركيبات الكهربائية',
            'HVAC Maintenance' => 'صيانة التهوية والتكييف',
            'Roofing' => 'الأسقف',
            'Carpentry' => 'النجارة',
            'Landscaping' => 'تنسيق الحدائق',
            
            // Professional & Business Services
            'Consulting' => 'الاستشارات',
            'Accounting' => 'المحاسبة',
            'Legal Services' => 'الخدمات القانونية',
            'Office Support' => 'دعم المكاتب',
            'Business Registration' => 'تسجيل الأعمال',
            'Tax Preparation' => 'تحضير الضرائب',
            
            // Personal & Lifestyle Services
            'Hair Salon' => 'صالون الشعر',
            'Makeup' => 'المكياج',
            'Spa' => 'منتجع صحي',
            'Fitness Training' => 'تدريب اللياقة البدنية',
            'Yoga' => 'اليوغا',
            'Massage' => 'التدليك',
            
            // Technical & Repair Services
            'Phone Repair' => 'إصلاح الهاتف',
            'Computer Repair' => 'إصلاح الكمبيوتر',
            'Appliance Repair' => 'إصلاح الأجهزة',
            'Electronics Repair' => 'إصلاح الإلكترونيات',
            
            // Event & Entertainment Services
            'DJ Services' => 'خدمات DJ',
            'Photography' => 'التصوير الفوتوغرافي',
            'Videography' => 'تصوير الفيديو',
            'Event Planning' => 'تنظيم الفعاليات',
            'Catering' => 'الوجبات الكاملة',
            'Music Lessons' => 'دروس الموسيقى',
            
            // Section names
            'Automotive Services' => 'خدمات السيارات',
            'Home & Property Services' => 'خدمات المنزل والممتلكات',
            'Professional & Business Services' => 'الخدمات المهنية والتجارية',
            'Personal & Lifestyle Services' => 'الخدمات الشخصية وأسلوب الحياة',
            'Technical & Repair Services' => 'الخدمات التقنية والإصلاحات',
            'Event & Entertainment Services' => 'خدمات الفعاليات والترفيه',
        ];

        foreach ($arabicTranslations as $english => $arabic) {
            DB::table('categories')
                ->where('name', $english)
                ->whereNull('name_ar')
                ->update(['name_ar' => $arabic]);
        }

        // For descriptions, we'll provide Arabic versions of common English descriptions
        $arabicDescriptions = [
            'Professional car maintenance and repair services' => 'خدمات صيانة وإصلاح السيارات الاحترافية',
            'Home cleaning and maintenance' => 'تنظيف وصيانة المنتزل',
            'Painting and decoration services' => 'خدمات الطلاء والزخرفة',
            'Plumbing and water services' => 'خدمات السباكة والمياه',
            'Professional business and consulting services' => 'خدمات الأعمال والاستشارات الاحترافية',
            'Personal grooming and care services' => 'خدمات العناية الشخصية والتجميل',
            'Electronics and appliance repair' => 'إصلاح الإلكترونيات والأجهزة',
            'Event planning and entertainment' => 'تنظيم الفعاليات والترفيه',
        ];

        foreach ($arabicDescriptions as $english => $arabic) {
            DB::table('categories')
                ->where('description', 'like', "%{$english}%")
                ->whereNull('description_ar')
                ->update(['description_ar' => $arabic]);
        }
    }

    /**
     * Populate French translations
     */
    private function populateFrenchTranslations(): void
    {
        $frenchTranslations = [
            // Automotive Services
            'Car Maintenance' => 'Entretien automobile',
            'Oil Changes' => 'Changements d\'huile',
            'Tire Services' => 'Services de pneus',
            'Car Wash' => 'Lavage automobile',
            'Detailing' => 'Détailing automobile',
            'Engine Diagnostics' => 'Diagnostic moteur',
            'Brake Service' => 'Service de freinage',
            'Battery Replacement' => 'Remplacement de batterie',
            'Electrical Repair' => 'Réparation électrique',
            
            // Home & Property Services
            'House Cleaning' => 'Nettoyage de maison',
            'Painting & Decoration' => 'Peinture et décoration',
            'Plumbing' => 'Plomberie',
            'Electrical Installation' => 'Installation électrique',
            'HVAC Maintenance' => 'Maintenance CVC',
            'Roofing' => 'Toiture',
            'Carpentry' => 'Menuiserie',
            'Landscaping' => 'Aménagement paysager',
            
            // Professional & Business Services
            'Consulting' => 'Conseil',
            'Accounting' => 'Comptabilité',
            'Legal Services' => 'Services juridiques',
            'Office Support' => 'Support de bureau',
            'Business Registration' => 'Enregistrement commercial',
            'Tax Preparation' => 'Préparation d\'impôts',
            
            // Personal & Lifestyle Services
            'Hair Salon' => 'Salon de coiffure',
            'Makeup' => 'Maquillage',
            'Spa' => 'Spa',
            'Fitness Training' => 'Entraînement physique',
            'Yoga' => 'Yoga',
            'Massage' => 'Massage',
            
            // Technical & Repair Services
            'Phone Repair' => 'Réparation de téléphone',
            'Computer Repair' => 'Réparation informatique',
            'Appliance Repair' => 'Réparation d\'appareils',
            'Electronics Repair' => 'Réparation d\'électronique',
            
            // Event & Entertainment Services
            'DJ Services' => 'Services DJ',
            'Photography' => 'Photographie',
            'Videography' => 'Cinématographie',
            'Event Planning' => 'Planification d\'événements',
            'Catering' => 'Restauration',
            'Music Lessons' => 'Cours de musique',
            
            // Section names
            'Automotive Services' => 'Services automobiles',
            'Home & Property Services' => 'Services à domicile et immobiliers',
            'Professional & Business Services' => 'Services professionnels et commerciaux',
            'Personal & Lifestyle Services' => 'Services personnels et mode de vie',
            'Technical & Repair Services' => 'Services techniques et réparation',
            'Event & Entertainment Services' => 'Services d\'événements et de divertissement',
        ];

        foreach ($frenchTranslations as $english => $french) {
            DB::table('categories')
                ->where('name', $english)
                ->whereNull('name_fr')
                ->update(['name_fr' => $french]);
        }

        // For descriptions
        $frenchDescriptions = [
            'Professional car maintenance and repair services' => 'Services professionnels d\'entretien et de réparation automobile',
            'Home cleaning and maintenance' => 'Nettoyage et entretien de maison',
            'Painting and decoration services' => 'Services de peinture et décoration',
            'Plumbing and water services' => 'Services de plomberie et d\'eau',
            'Professional business and consulting services' => 'Services commerciaux et de consultation professionnels',
            'Personal grooming and care services' => 'Services de soins personnels et de toilettage',
            'Electronics and appliance repair' => 'Réparation d\'électronique et d\'appareils',
            'Event planning and entertainment' => 'Planification d\'événements et divertissement',
        ];

        foreach ($frenchDescriptions as $english => $french) {
            DB::table('categories')
                ->where('description', 'like', "%{$english}%")
                ->whereNull('description_fr')
                ->update(['description_fr' => $french]);
        }
    }
};
