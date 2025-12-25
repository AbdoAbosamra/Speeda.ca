<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // جدول locations يستخدم enum للمدن المحددة مسبقاً
        $availableCities = ['Laval', 'Montreal', 'Ottawa', 'Gatineau'];

        return [
            'city' => fake()->randomElement($availableCities),
            'is_active' => true,
        ];
    }

    /**
     * Create an active location.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive location.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create location in specific province.
     */
    public function inProvince(string $province): static
    {
        return $this->state(fn (array $attributes) => [
            'province' => $province,
        ]);
    }
}
