<?php

namespace Database\Factories;

use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdminNotificationFactory extends Factory
{
    protected $model = AdminNotification::class;

    private const TITLES_AR = [
        'تنبيه هام لمقدمي الخدمات',
        'عروض وخصومات جديدة',
        'تحديثات المنصة',
        'تذكير بتحديث الملف الشخصي',
        'فرص عمل جديدة متاحة',
    ];

    private const TITLES_EN = [
        'Important Alert for Providers',
        'New Offers & Discounts',
        'Platform Updates',
        'Profile Update Reminder',
        'New Job Opportunities Available',
    ];

    private const TITLES_FR = [
        'Alerte Importante pour les Prestataires',
        'Nouvelles Offres & Réductions',
        'Mises à Jour de la Plateforme',
        'Rappel de Mise à Jour du Profil',
        'Nouvelles Opportunités',
    ];

    public function definition(): array
    {
        $idx = fake()->numberBetween(0, 4);

        return [
            'title_ar' => self::TITLES_AR[$idx],
            'title_en' => self::TITLES_EN[$idx],
            'title_fr' => self::TITLES_FR[$idx],
            'message_ar' => fake()->paragraph(),
            'message_en' => fake()->paragraph(),
            'message_fr' => fake()->paragraph(),
            'target_type' => 'provider_only',
            'created_by' => User::factory()->admin(),
            'expires_at' => fake()->dateTimeBetween('+1 day', '+30 days'),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn() => [
            'expires_at' => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn() => [
            'expires_at' => fake()->dateTimeBetween('+1 day', '+30 days'),
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn() => [
            'title_ar' => 'عاجل: تحديث هام',
            'title_en' => 'URGENT: Important Update',
            'title_fr' => 'URGENT: Mise à Jour Importante',
        ]);
    }

    public function createdBy(int $userId): static
    {
        return $this->state(fn() => ['created_by' => $userId]);
    }

    public function forTarget(string $targetType): static
    {
        return $this->state(fn() => ['target_type' => $targetType]);
    }
}
