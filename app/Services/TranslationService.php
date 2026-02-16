<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    /**
     * Translate text from English to target language
     *
     * @param  string  $text  Text to translate
     * @param  string  $targetLanguage  Target language code (ar, fr)
     * @return string|null Translated text or null on failure
     */
    public function translate(string $text, string $targetLanguage): ?string
    {
        if (empty(trim($text))) {
            return null;
        }

        // Use Google Translate API if configured
        if ($this->isGoogleTranslateConfigured()) {
            return $this->translateWithGoogle($text, $targetLanguage);
        }

        // ❌ REMOVED: Dangerous dictionary fallback
        // Dictionary approach breaks words (e.g., "Professional" → "Professفيonal")
        // SOLUTION: Return null and require manual translation in admin panel

        Log::info('Translation requested but Google Translate API not configured', [
            'text' => $text,
            'target' => $targetLanguage,
        ]);

        return null; // Require manual translation
    }

    /**
     * Check if Google Translate API is configured
     */
    protected function isGoogleTranslateConfigured(): bool
    {
        return ! empty(config('services.google_translate.api_key'));
    }

    /**
     * Translate using Google Translate API
     */
    protected function translateWithGoogle(string $text, string $targetLanguage): ?string
    {
        try {
            $apiKey = config('services.google_translate.api_key');
            $url = 'https://translation.googleapis.com/language/translate/v2';

            $response = Http::timeout(10)->post($url, [
                'key' => $apiKey,
                'q' => $text,
                'source' => 'en',
                'target' => $targetLanguage,
                'format' => 'text',
            ]);

            if ($response->successful() && isset($response->json()['data']['translations'][0]['translatedText'])) {
                return $response->json()['data']['translations'][0]['translatedText'];
            }

            Log::warning('Google Translate API error', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Translation service error', [
                'message' => $e->getMessage(),
                'text' => $text,
                'target' => $targetLanguage,
            ]);

            return null;
        }
    }

    /**
     * ❌ REMOVED: Dangerous dictionary fallback that breaks words
     *
     * The dictionary approach was fundamentally flawed because:
     * 1. Partial string matching breaks words (e.g., "in" inside "Professional" → "Professفيonal")
     * 2. Cannot handle context properly
     * 3. Unmaintainable for large vocabulary
     *
     * SOLUTION: Return null and require manual translation in admin panel
     * OR: Admin can use Google Translate API if configured
     */
    protected function translateWithDictionary(string $text, string $targetLanguage): ?string
    {
        // Dictionary fallback removed - too dangerous
        // Use Google Translate API or manual translation
        return null;
    }

    /**
     * Get translation dictionary for common terms
     */
    protected function getTranslationDictionary(): array
    {
        return [
            'ar' => [
                // --- Section names (أقسام رئيسية) ---
                'Automotive Services' => 'خدمات السيارات',
                'Home & Property Services' => 'خدمات المنزل والممتلكات',
                'Professional & Business Services' => 'الخدمات المهنية والتجارية',
                'Personal & Lifestyle Services' => 'الخدمات الشخصية وأسلوب الحياة',
                'Technical & Repair Services' => 'الخدمات التقنية والإصلاحات',
                'Event & Entertainment Services' => 'خدمات الفعاليات والترفيه',
                'Education & Tuition' => 'التعليم والتدريس الخصوصي', // إضافة
                'Pet Services' => 'خدمات الحيوانات الأليفة', // إضافة

                // --- Category names (الفئات) ---

                // Automotive
                'Car Mechanics' => 'ميكانيكا السيارات',
                'Oil Change Services' => 'تغيير الزيت',
                'Electric & Hybrid Car Services' => 'سيارات كهربائية وهجينة',
                'Tire Change & Repair' => 'إطارات (تغيير وإصلاح)',
                'Car Dealers' => 'تجار السيارات',
                'Cars Inspections (Safety) for Uber' => 'فحص أوبر (الأمان)',
                'Auto Body Repair' => 'سمكرة وإصلاح صدمات',
                'Car Wash & Detailing' => 'غسيل وتلميع السيارات',
                'Roadside Assistance' => 'المساعدة على الطريق', // إضافة
                'Towing Services' => 'خدمات سحب السيارات (ونش)', // إضافة

                // Home & Property
                'Roofing Contractors' => 'مقاولات الأسقف',
                'Carpentry Services' => 'أعمال النجارة',
                'Painting Services' => 'دهانات وأصباغ',
                'Plumbing Services' => 'سباكة وصرف صحي',
                'Electrical Technicians' => 'فني كهرباء',
                'Handyman Services' => 'صيانة منزلية عامة',
                'Moving Services' => 'نقل عفش وتغليف',
                'Cleaning Services' => 'خدمات تنظيف',
                'Landscaping & Gardening' => 'تنسيق حدائق',
                'Home Renovation' => 'ترميم وتجديد منازل',
                'Pest Control' => 'مكافحة حشرات',
                'Security System Installation' => 'كاميرات وأنظمة أمان',
                'Snow Removal' => 'إزالة ثلوج',
                'HVAC Services' => 'تكييف وتدفئة وتبريد',

                // Business & Legal
                'Accounting & Bookkeeping' => 'محاسبة ومسك دفاتر',
                'Insurance Brokers' => 'وسطاء تأمين',
                'Lawyers & Legal Advisors' => 'محامون واستشارات قانونية',
                'Translators & Interpreters' => 'ترجمة وترجمة فورية',
                'Real Estate Agents' => 'تسويق عقاري',
                'Marketing & Advertising' => 'تسويق وإعلان',
                'Notary Public' => 'توثيق مستندات / كاتب عدل', // إضافة

                // Personal & Lifestyle
                'Beauty & Personal Care' => 'تجميل وعناية',
                'Restaurants & Catering' => 'مطاعم وتجهيز بوفيه',
                'Dental & Oral Care' => 'طب وعناية الأسنان',
                'Fitness Trainers' => 'مدربو لياقة بدنية',
                'Massage Therapy' => 'جلسات مساج وعلاج طبيعي',
                'Hair Stylists' => 'تصفيف شعر',
                'Makeup Artists' => 'خبيرات تجميل (مكياج)',
                'Event Planners' => 'تنظيم حفلات ومناسبات',
                'Barber' => 'حلاق رجالي',
                'Pet Grooming' => 'حلاقة وتنظيف حيوانات أليفة', // إضافة

                // Technical
                'Appliance Repair' => 'صيانة أجهزة منزلية',
                'Computer Repair' => 'صيانة كمبيوتر ولابتوب',
                'Phone Repair' => 'صيانة جوالات',
                'AC & Refrigeration' => 'تكييف وتبريد',
                'Generator Repair' => 'صيانة مولدات كهرباء',

                // Events
                'Photographers' => 'تصوير فوتوغرافي',
                'Videographers' => 'تصوير فيديو',
                'DJs & Music' => 'دي جي وموسيقى',
                'Catering Services' => 'تموين وحفلات',
                'Decorators' => 'ديكور وتزيين مناسبات',
                'Entertainers' => 'عروض ترفيهية',

                // Others
                'Others' => 'أخرى',
                'Professional' => 'احترافي',
                'services' => 'خدمات',
                'in' => 'في',
            ],
            'fr' => [
                // سأقوم بتحديث القسم الفرنسي ليشمل الإضافات الجديدة أيضاً
                'Education & Tuition' => 'Éducation et cours privés',
                'Pet Services' => 'Services pour animaux',
                'Roadside Assistance' => 'Assistance routière',
                'Towing Services' => 'Services de remorquage',
                'Notary Public' => 'Notaire / Serment',
                'Pet Grooming' => 'Toilettage d\'animaux',
                // ... (بقية القيم الفرنسية التي كانت لديك تظل كما هي)
                'Automotive Services' => 'Services automobiles',
                'Home & Property Services' => 'Services à domicile et immobiliers',
                'Professional & Business Services' => 'Services professionnels et commerciaux',
                'Personal & Lifestyle Services' => 'Services personnels et mode de vie',
                'Technical & Repair Services' => 'Services techniques et réparation',
                'Event & Entertainment Services' => 'Services d\'événements et de divertissement',
                'Car Mechanics' => 'Mécaniciens automobiles',
                'Oil Change Services' => 'Services de changement d\'huile',
                'Electric & Hybrid Car Services' => 'Services pour voitures électriques et hybrides',
                'Tire Change & Repair' => 'Changement et réparation de pneus',
                'Car Dealers' => 'Concessionnaires automobiles',
                'Cars Inspections (Safety) for Uber' => 'Inspections de sécurité pour Uber',
                'Auto Body Repair' => 'Réparation de carrosserie',
                'Car Wash & Detailing' => 'Lavage et détailing automobile',
                'Others' => 'Autres',
                'Roofing Contractors' => 'Entrepreneurs en toiture',
                'Carpentry Services' => 'Services de menuiserie',
                'Painting Services' => 'Services de peinture',
                'Plumbing Services' => 'Services de plomberie',
                'Electrical Technicians' => 'Techniciens électriciens',
                'Handyman Services' => 'Services de bricolage',
                'Moving Services' => 'Services de déménagement',
                'Cleaning Services' => 'Services de nettoyage',
                'Landscaping & Gardening' => 'Aménagement paysager et jardinage',
                'Home Renovation' => 'Rénovation domiciliaire',
                'Pest Control' => 'Lutte antiparasitaire',
                'Security System Installation' => 'Installation de systèmes de sécurité',
                'Snow Removal' => 'Déneigement',
                'HVAC Services' => 'Services CVC',
                'Accounting & Bookkeeping' => 'Comptabilité et tenue de livres',
                'Insurance Brokers' => 'Courtiers d\'assurance',
                'Lawyers & Legal Advisors' => 'Avocats et conseillers juridiques',
                'Translators & Interpreters' => 'Traducteurs et interprètes',
                'Real Estate Agents' => 'Agents immobiliers',
                'Marketing & Advertising' => 'Marketing et publicité',
                'Beauty & Personal Care' => 'Beauté et soins personnels',
                'Restaurants & Catering' => 'Restaurants et traiteur',
                'Dental & Oral Care' => 'Soins dentaires et bucco-dentaires',
                'Fitness Trainers' => 'Entraîneurs de fitness',
                'Massage Therapy' => 'Massothérapie',
                'Hair Stylists' => 'Coiffeurs',
                'Makeup Artists' => 'Maquilleurs',
                'Event Planners' => 'Planificateurs d\'événements',
                'Barber' => 'Barbier',
                'Appliance Repair' => 'Réparation d\'appareils',
                'Computer Repair' => 'Réparation d\'ordinateurs',
                'Phone Repair' => 'Réparation de téléphones',
                'AC & Refrigeration' => 'Climatisation et réfrigération',
                'Generator Repair' => 'Réparation de générateurs',
                'Photographers' => 'Photographes',
                'Videographers' => 'Vidéastes',
                'DJs & Music' => 'DJ et musique',
                'Catering Services' => 'Services de traiteur',
                'Decorators' => 'Décorateurs',
                'Entertainers' => 'Artistes',
                'Professional' => 'Professionnel',
                'services' => 'services',
                'in' => 'dans',
            ],
        ];
    }
}
