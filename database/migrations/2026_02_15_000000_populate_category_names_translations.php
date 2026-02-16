<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Comprehensive category translations
        $translations = [
            // Automotive Services Section
            1 => ['ar' => 'خدمات السيارات', 'fr' => 'Services automobiles'],

            // Automotive Categories
            7 => ['ar' => 'ميكانيكا السيارات', 'fr' => 'Mécanique automobile'],
            8 => ['ar' => 'خدمات تغيير الزيت', 'fr' => 'Services de changement d\'huile'],
            9 => ['ar' => 'خدمات السيارات الكهربائية والهجينة', 'fr' => 'Services de véhicules électriques et hybrides'],
            10 => ['ar' => 'ضبط وزن الإطارات وضبط زاوية العجلات', 'fr' => 'Équilibrage des pneus et géométrie'],
            11 => ['ar' => 'وكلاء السيارات', 'fr' => 'Concessionnaires automobiles'],
            12 => ['ar' => 'فحوصات السيارات (السلامة) لأوبر', 'fr' => 'Inspections de véhicules (Sécurité) pour Uber'],
            13 => ['ar' => 'إصلاح هيكل السيارة', 'fr' => 'Réparation de carrosserie'],
            14 => ['ar' => 'غسيل وتفصيل السيارات', 'fr' => 'Lavage et détailing automobile'],
            65 => ['ar' => 'خدمة فتح السيارات المغلقة', 'fr' => 'Service de déverrouillage d\'automobile'],
            68 => ['ar' => 'مساعدة على الطريق (24/7)', 'fr' => 'Assistance routière (24/7)'],

            // Home & Property Services Section
            2 => ['ar' => 'خدمات المنزل والممتلكات', 'fr' => 'Services résidentiels et immobiliers'],

            // Home & Property Categories
            16 => ['ar' => 'مقاولو التسقيف', 'fr' => 'Entrepreneurs en toiture'],
            17 => ['ar' => 'خدمات النجارة', 'fr' => 'Services de menuiserie'],
            18 => ['ar' => 'خدمات الدهانات', 'fr' => 'Services de peinture'],
            19 => ['ar' => 'خدمات السباكة', 'fr' => 'Services de plomberie'],
            20 => ['ar' => 'الفنيون الكهربائيون', 'fr' => 'Techniciens électriques'],
            21 => ['ar' => 'خدمات الصيانة العامة', 'fr' => 'Services de bricolage'],
            22 => ['ar' => 'خدمات النقل', 'fr' => 'Services de déménagement'],
            23 => ['ar' => 'خدمات التنظيف', 'fr' => 'Services de nettoyage'],
            24 => ['ar' => 'تنسيق الحدائق والبستنة', 'fr' => 'Services d\'aménagement paysager'],
            25 => ['ar' => 'تجديد المنازل', 'fr' => 'Rénovation résidentielle'],
            26 => ['ar' => 'مكافحة الآفات', 'fr' => 'Lutte antiparasitaire'],
            27 => ['ar' => 'تركيب أنظمة الأمان', 'fr' => 'Installation de systèmes de sécurité'],
            28 => ['ar' => 'إزالة الثلوج', 'fr' => 'Enlèvement de neige'],
            29 => ['ar' => 'خدمات التكييف والتهوية', 'fr' => 'Services de CVC'],
            69 => ['ar' => 'إصلاح الأجهزة المنزلية', 'fr' => 'Réparation d\'appareils électroménagers'],
            70 => ['ar' => 'تركيب وإصلاح الأرضيات', 'fr' => 'Installation et réparation de planchers'],
            71 => ['ar' => 'الإصلاحات والصيانة', 'fr' => 'Réparations et entretien'],
            73 => ['ar' => 'تركيب وإصلاح الأسوار', 'fr' => 'Installation et réparation de clôtures'],
            74 => ['ar' => 'إزالة النفايات', 'fr' => 'Enlèvement de débris'],
            75 => ['ar' => 'ترميم أضرار المياه', 'fr' => 'Restauration des dégâts d\'eau'],
            76 => ['ar' => 'تركيب وإصلاح أبواب الجراج', 'fr' => 'Installation et réparation de portes de garage'],

            // Professional & Business Services Section
            3 => ['ar' => 'الخدمات المهنية والتجارية', 'fr' => 'Services professionnels et commerciaux'],

            // Professional Categories
            31 => ['ar' => 'المحاسبة والمسك الدفاتر + إعداد الضرائب', 'fr' => 'Comptabilité et tenue de livres + Préparation de déclarations d\'impôts'],
            32 => ['ar' => 'وسطاء التأمين', 'fr' => 'Courtiers d\'assurance'],
            33 => ['ar' => 'المحامون والمستشارون القانونيون', 'fr' => 'Avocats et conseillers juridiques'],
            34 => ['ar' => 'المترجمون والمترجمون الشفهيون', 'fr' => 'Traducteurs et interprètes'],
            35 => ['ar' => 'وكلاء العقارات', 'fr' => 'Agents immobiliers'],
            36 => ['ar' => 'التسويق والإعلان', 'fr' => 'Marketing et publicité'],
            78 => ['ar' => 'الموارد البشرية والتوظيف', 'fr' => 'Ressources humaines et recrutement'],
            79 => ['ar' => 'دعم تكنولوجيا المعلومات', 'fr' => 'Support informatique'],
            80 => ['ar' => 'تصميم المواقع', 'fr' => 'Conception de sites Web'],
            81 => ['ar' => 'التصميم الجرافيكي', 'fr' => 'Design graphique'],
            82 => ['ar' => 'كاتب عدل', 'fr' => 'Notaire'],
            83 => ['ar' => 'خدمات الطباعة', 'fr' => 'Services d\'impression'],

            // Personal & Lifestyle Services Section
            4 => ['ar' => 'الخدمات الشخصية وأسلوب الحياة', 'fr' => 'Services personnels et mode de vie'],

            // Personal Categories
            38 => ['ar' => 'الجمال والعناية الشخصية', 'fr' => 'Beauté et soins personnels'],
            39 => ['ar' => 'المطاعم والوجبات الكاملة', 'fr' => 'Restaurants et services de restauration'],
            40 => ['ar' => 'رعاية الأسنان والفم', 'fr' => 'Soins dentaires et buccaux'],
            41 => ['ar' => 'مدربو اللياقة البدنية', 'fr' => 'Entraîneurs personnels de fitness'],
            42 => ['ar' => 'علاج التدليك', 'fr' => 'Massothérapie'],
            43 => ['ar' => 'مصففو الشعر', 'fr' => 'Coiffeurs'],
            44 => ['ar' => 'فنانو المكياج', 'fr' => 'Artistes en maquillage'],
            46 => ['ar' => 'الحلاقون', 'fr' => 'Barbiers'],
            84 => ['ar' => 'فناني الوشم والثقب', 'fr' => 'Artistes du tatouage et du piercing'],
            85 => ['ar' => 'العناية بالحيوانات الأليفة', 'fr' => 'Toilettage d\'animaux'],
            86 => ['ar' => 'رعاية الأطفال / جليسة أطفال', 'fr' => 'Garderie/Babysitting'],

            // Technical & Repair Services Section
            5 => ['ar' => 'الخدمات التقنية والإصلاحات', 'fr' => 'Services techniques et réparations'],

            // Technical Categories
            49 => ['ar' => 'إصلاح أجهزة الكمبيوتر', 'fr' => 'Réparation d\'ordinateurs'],
            50 => ['ar' => 'إصلاح الهواتف', 'fr' => 'Réparation de téléphones'],
            51 => ['ar' => 'التكييف والتبريد', 'fr' => 'Climatisation et réfrigération'],
            52 => ['ar' => 'إصلاح المولدات', 'fr' => 'Réparation de génératrices'],
            87 => ['ar' => 'التلفزيون وخدمات البث المباشر', 'fr' => 'Services de télévision et de diffusion en continu'],
            88 => ['ar' => 'إصلاح وصيانة الإلكترونيات', 'fr' => 'Réparation et entretien d\'électronique'],

            // Event & Entertainment Services Section
            6 => ['ar' => 'خدمات الفعاليات والترفيه', 'fr' => 'Services d\'événements et de divertissement'],

            // Event Categories
            54 => ['ar' => 'المصورون', 'fr' => 'Photographes'],
            55 => ['ar' => 'مصورو الفيديو', 'fr' => 'Vidéographes'],
            56 => ['ar' => 'دي جي والموسيقى', 'fr' => 'DJ et musique'],
            57 => ['ar' => 'خدمات تقديم الطعام', 'fr' => 'Services de catering'],
            58 => ['ar' => 'مصممو الديكور', 'fr' => 'Décorateurs'],
            59 => ['ar' => 'منظمو الفعاليات', 'fr' => 'Organisateurs d\'événements'],
            60 => ['ar' => 'الفنانون', 'fr' => 'Artistes/Divertisseurs'],

            // Others Section
            62 => ['ar' => 'أخرى', 'fr' => 'Autres'],
            63 => ['ar' => 'أخرى', 'fr' => 'Autres'],
        ];

        // Update all categories
        foreach ($translations as $id => $langs) {
            DB::table('categories')
                ->where('id', $id)
                ->update([
                    'name_ar' => $langs['ar'],
                    'name_fr' => $langs['fr'],
                ]);
        }
    }

    public function down(): void
    {
        DB::table('categories')->update([
            'name_ar' => null,
            'name_fr' => null,
        ]);
    }
};
