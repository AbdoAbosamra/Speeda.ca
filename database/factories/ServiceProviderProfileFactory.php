<?php

namespace Database\Factories;

use App\Models\ServiceProviderProfile;
use App\Models\ServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceProviderProfile>
 */
class ServiceProviderProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServiceProviderProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'category_id' => \App\Models\Category::factory(),
            'location_id' => fake()->numberBetween(1, 4), // Use existing seeded locations
            'profession' => fake()->jobTitle(),
            'bio' => fake()->paragraph(3),
            'services_offered' => fake()->text(200),
            'hourly_rate' => fake()->randomFloat(2, 25, 150),
            'experience_years' => fake()->numberBetween(0, 25),
            'phone' => $this->generateCanadianPhone(),
            'facebook' => fake()->optional()->url(),
            'instagram' => fake()->optional()->url(),
            'linkedin' => fake()->optional()->url(),
            'service_area' => fake()->city() . ' and surrounding areas',
            'available_weekends' => fake()->boolean(),
            'available_evenings' => fake()->boolean(),
            'availability_schedule' => json_encode([
                'monday' => ['9:00-17:00'],
                'tuesday' => ['9:00-17:00'],
                'wednesday' => ['9:00-17:00'],
                'thursday' => ['9:00-17:00'],
                'friday' => ['9:00-17:00']
            ]),
            'certifications' => json_encode([fake()->word() . ' Certificate']),
            'languages' => json_encode([fake()->randomElement(['English', 'French', 'Arabic'])]),
            'specializations' => json_encode([fake()->word(), fake()->word()]),
            'profile_image' => null,
            'portfolio_images' => json_encode([]),
            'portfolio_videos' => json_encode([]),
            'is_verified' => fake()->boolean(60),
            'is_featured' => fake()->boolean(20),
            'business_type' => fake()->randomElement(['individual', 'company']),
            'company_name' => fake()->optional()->company(),
            'business_license' => fake()->optional()->word(),
            'completed_jobs' => fake()->numberBetween(0, 50),
            'views' => fake()->numberBetween(0, 1000),
            'emergency_available' => fake()->boolean(30),
            'response_time_hours' => fake()->optional()->numberBetween(1, 24),
        ];
    }

    /**
     * Generate a Canadian phone number.
     */
    private function generateCanadianPhone(): string
    {
        $areaCodes = [
            // Major Canadian area codes
            '416', '647', '437', // Toronto
            '514', '438', // Montreal
            '604', '778', '236', // Vancouver
            '403', '587', '825', // Calgary
            '613', '343', // Ottawa
            '705', '249', // Northern Ontario
            '519', '548', '226', // Southwestern Ontario
            '905', '289', '365', // Greater Toronto Area
            '807', // Northwestern Ontario
        ];

        $areaCode = fake()->randomElement($areaCodes);
        $exchange = fake()->numberBetween(200, 999);
        $number = fake()->numberBetween(1000, 9999);

        return "+1{$areaCode}{$exchange}{$number}";
    }

    /**
     * Create a featured profile.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Create a verified profile.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
        ]);
    }

    /**
     * Create a profile with emergency services.
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'emergency_available' => true,
        ]);
    }
}
