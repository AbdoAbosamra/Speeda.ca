<?php

namespace Database\Factories;

use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceProvider>
 */
class ServiceProviderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServiceProvider::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'location_id' => fake()->numberBetween(1, 4), // Use existing seeded locations (1-4)
            'is_certified' => fake()->boolean(40),
            'certification' => fake()->optional()->word(),
            'bio' => fake()->paragraph(3),
            'services_offered' => fake()->paragraph(2),
            'hourly_rate' => fake()->randomFloat(2, 25, 150),
            'experience_years' => fake()->numberBetween(0, 25),
            'phone' => $this->generateCanadianPhone(),
            'whatsapp_number' => fake()->optional()->numerify('+1##########'),
            'contact_email' => fake()->optional()->email(),
            'address' => fake()->optional()->address(),
            'facebook' => fake()->optional()->url(),
            'instagram' => fake()->optional()->url(),
            'linkedin' => fake()->optional()->url(),
            'service_area' => fake()->city(),
            'available_weekends' => fake()->boolean(),
            'available_evenings' => fake()->boolean(),
            'availability_schedule' => json_encode(['monday' => '9-17', 'tuesday' => '9-17']),
            'languages' => json_encode([fake()->randomElement(['English', 'French', 'Arabic'])]),
            'specializations' => json_encode([fake()->word(), fake()->word()]),
            'profile_image' => null,
            'portfolio_images' => json_encode([]),
            'portfolio_videos' => json_encode([]),
            'is_verified' => fake()->boolean(60),
            'is_featured' => fake()->boolean(20),
            'business_type' => fake()->randomElement(['individual', 'company']),
            'company_name' => fake()->optional()->company(),
            'business_license' => fake()->optional()->regexify('[A-Z0-9]{10}'),
            'rating' => fake()->randomFloat(2, 1, 5),
            'total_reviews' => fake()->numberBetween(0, 100),
            'completed_jobs' => fake()->numberBetween(0, 50),
            'views' => fake()->numberBetween(0, 1000),
            'emergency_available' => fake()->boolean(30),
            'response_time_hours' => fake()->optional()->numberBetween(1, 48),
        ];
    }

    /**
     * Create a featured service provider.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Create a verified service provider.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
        ]);
    }

    /**
     * Create an unverified service provider.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
        ]);
    }

    /**
     * Set specific category.
     */
    public function forCategory(int $categoryId): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $categoryId,
        ]);
    }

    /**
     * Set specific user.
     */
    public function forUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    /**
     * Create with high view count.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'view_count' => fake()->numberBetween(500, 5000),
        ]);
    }

    /**
     * Create with specific city.
     */
    public function inCity(string $city): static
    {
        return $this->state(fn (array $attributes) => [
            'city' => $city,
        ]);
    }

    /**
     * Create with WhatsApp number.
     */
    public function withWhatsApp(): static
    {
        return $this->state(fn (array $attributes) => [
            'whatsapp_number' => $this->generateCanadianPhone(),
        ]);
    }

    /**
     * Create without WhatsApp number.
     */
    public function withoutWhatsApp(): static
    {
        return $this->state(fn (array $attributes) => [
            'whatsapp_number' => null,
        ]);
    }

    /**
     * Generate a valid Canadian phone number.
     */
    private function generateCanadianPhone(): string
    {
        $areaCodes = ['416', '647', '437', '514', '438', '613', '819', '905', '289', '365', '226', '519', '548', '249', '705', '807'];
        $areaCode = fake()->randomElement($areaCodes);

        // Generate exchange (first 3 digits after area code)
        // Cannot start with 0 or 1
        $exchange = fake()->numberBetween(200, 999);

        // Generate line number (last 4 digits)
        $lineNumber = fake()->numberBetween(1000, 9999);

        return "+1{$areaCode}{$exchange}{$lineNumber}";
    }

    /**
     * Create with specific hourly rate.
     */
    public function withHourlyRate(float $rate): static
    {
        return $this->state(fn (array $attributes) => [
            'hourly_rate' => $rate,
        ]);
    }

    /**
     * Create with specific experience years.
     */
    public function withExperience(int $years): static
    {
        return $this->state(fn (array $attributes) => [
            'experience_years' => $years,
        ]);
    }
}
