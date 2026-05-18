<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    private const ARABIC_REVIEWS = [
        'خدمة ممتازة وسريعة، العامل كان محترفاً جداً وأنجز العمل في الوقت المحدد.',
        'تجربة رائعة، سأوصي بهم لكل من يحتاج هذه الخدمة. جودة عالية وسعر مناسب.',
        'العمل تم بشكل احترافي تام، لا يوجد أي شيء للشكوى منه. أنصح بالتعامل معهم.',
        'خدمة جيدة ولكن التأخير في الوصول كان ملحوظاً. الجودة مقبولة بشكل عام.',
        'لم أكن راضياً عن جودة العمل، المشكلة لم تُحل بشكل كامل.',
        'سرعة في الاستجابة، الفريق محترف ومنظم، والنتيجة فاقت توقعاتي.',
        'تعامل راقي وأسعار مناسبة. سأتعامل معهم مرة أخرى بالتأكيد.',
        'أداء ممتاز والتزام بالمواعيد. أنصح الجميع بالتعامل مع هذه المؤسسة.',
    ];

    private const ENGLISH_REVIEWS = [
        'Excellent service! Highly recommend.',
        'Very professional and on time. Great quality work.',
        'Good service but slightly overpriced.',
        'Outstanding work, exceeded expectations.',
        'Average experience, nothing special.',
        'Would not recommend. Poor communication.',
        'Perfect job, will definitely use again.',
        'Amazing service, very responsive team.',
    ];

    public function definition(): array
    {
        return [
            'service_provider_id' => ServiceProvider::factory(),
            'client_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'review_text' => fake()->paragraph(),
            'is_active' => false,
            'is_verified' => fake()->boolean(70),
            'is_featured' => fake()->boolean(10),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn() => [
            'is_active' => false,
            'admin_approved_at' => null,
            'admin_approved_by' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn() => [
            'is_active' => true,
            'admin_approved_at' => fake()->dateTimeBetween('-60 days', '-1 day'),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn() => [
            'is_active' => false,
            'admin_approved_at' => fake()->dateTimeBetween('-60 days', '-1 day'),
        ]);
    }

    public function fiveStar(): static
    {
        return $this->state(fn() => ['rating' => 5]);
    }

    public function oneStar(): static
    {
        return $this->state(fn() => ['rating' => 1]);
    }

    public function withRating(int $rating): static
    {
        return $this->state(fn() => ['rating' => max(1, min(5, $rating))]);
    }

    public function arabic(): static
    {
        return $this->state(fn() => [
            'review_text' => fake()->randomElement(self::ARABIC_REVIEWS),
        ]);
    }

    public function english(): static
    {
        return $this->state(fn() => [
            'review_text' => fake()->randomElement(self::ENGLISH_REVIEWS),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn() => ['is_featured' => true]);
    }

    public function forProvider(int $providerId): static
    {
        return $this->state(fn() => ['service_provider_id' => $providerId]);
    }

    public function byClient(int $clientId): static
    {
        return $this->state(fn() => ['client_id' => $clientId]);
    }
}
