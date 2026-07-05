<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceProviderFactory extends Factory
{
    protected $model = ServiceProvider::class;

    private const ARABIC_COMPANY_NAMES = [
        'شركة النجم للخدمات المنزلية', 'مؤسسة الإتقان للصيانة', 'أعمال الخبراء للبناء',
        'شركة الثقة للخدمات الكهربائية', 'مؤسسة الجودة للسباكة', 'خبراء التكنولوجيا الذكية',
        'شركة الأمانة للنجارة', 'مؤسسة البناء الحديث', 'شركة الخدمات المثالية',
    ];

    private const ARABIC_BIOS = [
        'نقدم خدمات احترافية بأعلى معايير الجودة مع خبرة تتجاوز عشر سنوات في المجال.',
        'متخصصون في تقديم أفضل الحلول لعملائنا مع ضمان الرضا التام والجودة العالية.',
        'فريق متخصص ومحترف يعمل على مدار الساعة لتلبية احتياجاتكم بكفاءة عالية.',
        'خبرة واسعة في المجال مع التزام تام بالمواعيد ومعايير السلامة المهنية.',
        'نضمن لكم أفضل النتائج بأفضل الأسعار، مع خدمة عملاء متميزة على مدار الساعة.',
    ];

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'location_id' => Location::factory(),
            'company_name' => fake()->company(),
            'bio' => fake()->paragraph(3),
            'services_offered' => fake()->paragraph(2),
            'experience_years' => fake()->numberBetween(1, 25),
            'hourly_rate' => fake()->randomFloat(2, 25, 150),
            'phone' => $this->generateCanadianPhone(),
            'whatsapp_number' => fake()->optional(0.7)->numerify('+1##########'),
            'address' => fake()->optional(0.8)->address(),
            'is_verified' => fake()->boolean(60),
            'business_type' => fake()->randomElement(['individual', 'company']),
            'business_license' => fake()->optional(0.3)->regexify('[A-Z0-9]{10}'),
            'languages' => fake()->randomElements(['en', 'fr', 'ar'], fake()->numberBetween(1, 2)),
            'specializations' => [fake()->word(), fake()->word()],
            'available_weekends' => fake()->boolean(50),
            'available_evenings' => fake()->boolean(40),
            'emergency_available' => fake()->boolean(30),
            'response_time_hours' => fake()->optional(0.7)->numberBetween(1, 48),
            'is_certified' => fake()->boolean(40),
            'certification' => fake()->optional(0.5)->randomElement(['Licensed', 'Certified', 'Bonded']),
            'rating' => fake()->randomFloat(2, 1, 5),
            'calculated_rating' => fake()->randomFloat(2, 1, 5),
            'views' => fake()->numberBetween(0, 1000),
            'endorsement_count' => fake()->numberBetween(0, 50),
            'profile_completion_percent' => fake()->numberBetween(30, 100),
            'availability_schedule' => ['monday' => '9-17', 'tuesday' => '9-17', 'wednesday' => '9-17'],
            'service_area' => fake()->city(),
            'instagram' => fake()->optional(0.3)->url(),
            'facebook' => fake()->optional(0.3)->url(),
        ];
    }

    public function complete(): static
    {
        return $this->state(fn() => [
            'bio' => fake()->paragraph(5),
            'services_offered' => fake()->paragraph(4),
            'experience_years' => fake()->numberBetween(5, 25),
            'hourly_rate' => fake()->randomFloat(2, 40, 200),
            'phone' => $this->generateCanadianPhone(),
            'whatsapp_number' => $this->generateCanadianPhone(),
            'address' => fake()->address(),
            'languages' => ['en', 'fr', 'ar'],
            'specializations' => [fake()->word(), fake()->word(), fake()->word()],
            'available_weekends' => true,
            'available_evenings' => true,
            'emergency_available' => true,
            'response_time_hours' => fake()->numberBetween(1, 4),
            'is_certified' => true,
            'certification' => 'Licensed & Certified',
            'business_license' => fake()->regexify('[A-Z]{2}[0-9]{6}'),
            'profile_completion_percent' => 100,
            'is_verified' => true,
        ]);
    }

    public function partial(): static
    {
        return $this->state(fn() => [
            'bio' => fake()->paragraph(2),
            'services_offered' => fake()->paragraph(1),
            'experience_years' => fake()->numberBetween(1, 10),
            'phone' => $this->generateCanadianPhone(),
            'whatsapp_number' => null,
            'contact_email' => null,
            'address' => fake()->address(),
            'languages' => fake()->randomElements(['en', 'fr'], 1),
            'available_weekends' => fake()->boolean(),
            'profile_completion_percent' => fake()->numberBetween(60, 80),
        ]);
    }

    public function minimal(): static
    {
        return $this->state(fn() => [
            'bio' => null,
            'services_offered' => null,
            'experience_years' => null,
            'hourly_rate' => null,
            'phone' => $this->generateCanadianPhone(),
            'whatsapp_number' => null,
            'contact_email' => null,
            'address' => null,
            'languages' => null,
            'specializations' => null,
            'available_weekends' => false,
            'available_evenings' => false,
            'emergency_available' => false,
            'response_time_hours' => null,
            'is_certified' => false,
            'certification' => null,
            'business_license' => null,
            'profile_completion_percent' => fake()->numberBetween(0, 40),
            'is_verified' => false,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['is_verified' => false]);
    }

    public function topRated(): static
    {
        return $this->state(fn() => [
            'rating' => fake()->randomFloat(2, 4.5, 5.0),
            'calculated_rating' => fake()->randomFloat(2, 4.5, 5.0),
        ]);
    }

    public function lowRated(): static
    {
        return $this->state(fn() => [
            'rating' => fake()->randomFloat(2, 1.0, 2.4),
            'calculated_rating' => fake()->randomFloat(2, 1.0, 2.4),
        ]);
    }

    public function noReviews(): static
    {
        return $this->state(fn() => [
            'rating' => 0.00,
            'calculated_rating' => 0.00,
        ]);
    }

    public function arabic(): static
    {
        return $this->state(fn() => [
            'company_name' => fake()->randomElement(self::ARABIC_COMPANY_NAMES),
            'bio' => fake()->randomElement(self::ARABIC_BIOS),
        ]);
    }

    public function withPhone(): static
    {
        return $this->state(fn() => ['phone' => $this->generateCanadianPhone()]);
    }

    public function withWhatsapp(): static
    {
        return $this->state(fn() => ['whatsapp_number' => $this->generateCanadianPhone()]);
    }

    public function forCategory(int $categoryId): static
    {
        return $this->state(fn() => ['category_id' => $categoryId]);
    }

    public function forUser(int $userId): static
    {
        return $this->state(fn() => ['user_id' => $userId]);
    }

    public function forLocation(int $locationId): static
    {
        return $this->state(fn() => ['location_id' => $locationId]);
    }

    public function popular(): static
    {
        return $this->state(fn() => ['views' => fake()->numberBetween(500, 5000)]);
    }

    public function withRating(float $min, float $max): static
    {
        return $this->state(fn() => [
            'rating' => fake()->randomFloat(2, $min, $max),
            'calculated_rating' => fake()->randomFloat(2, $min, $max),
        ]);
    }

    public function testData(int $index = 1): static
    {
        return $this->state(fn() => [
            'user_id' => User::factory()->testData('provider', $index)->serviceProvider(),
            'rating' => fake()->randomFloat(1, 1, 5),
        ]);
    }

    private function generateCanadianPhone(): string
    {
        $areaCodes = ['416', '647', '437', '514', '438', '613', '819', '905', '289', '365', '226', '519'];
        return '+1' . fake()->randomElement($areaCodes) . fake()->numberBetween(200, 999) . fake()->numberBetween(1000, 9999);
    }
}
