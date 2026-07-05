<?php

namespace Database\Factories;

use App\Models\LegalPage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LegalPageFactory extends Factory
{
    protected $model = LegalPage::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'slug' => Str::slug($title),
            'page_type' => LegalPage::TYPE_CUSTOM,
            'status' => LegalPage::STATUS_DRAFT,
            'allow_indexing' => true,
            'published_at' => null,
            'last_reviewed_at' => null,
            'title_en' => $title,
            'title_ar' => 'صفحة قانونية',
            'title_fr' => 'Page juridique',
            'content_en' => '<h2>Overview</h2><p>English legal content.</p>',
            'content_ar' => '<h2>نظرة عامة</h2><p>محتوى قانوني عربي.</p>',
            'content_fr' => '<h2>Aperçu</h2><p>Contenu juridique français.</p>',
            'summary_en' => 'English legal summary.',
            'summary_ar' => 'ملخص قانوني عربي.',
            'summary_fr' => 'Résumé juridique français.',
            'created_by' => User::factory()->client(),
            'updated_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => LegalPage::STATUS_PUBLISHED,
            'published_at' => now()->subMinute(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => LegalPage::STATUS_DRAFT,
            'published_at' => null,
        ]);
    }

    public function privacyPolicy(): static
    {
        return $this->state(fn () => [
            'slug' => 'privacy-policy',
            'page_type' => LegalPage::TYPE_PRIVACY_POLICY,
            'title_en' => 'Privacy Policy',
            'title_ar' => 'سياسة الخصوصية',
            'title_fr' => 'Politique de confidentialité',
        ]);
    }

    public function termsOfService(): static
    {
        return $this->state(fn () => [
            'slug' => 'terms-of-service',
            'page_type' => LegalPage::TYPE_TERMS_OF_SERVICE,
            'title_en' => 'Terms of Service',
            'title_ar' => 'شروط الخدمة',
            'title_fr' => 'Conditions d’utilisation',
        ]);
    }
}
