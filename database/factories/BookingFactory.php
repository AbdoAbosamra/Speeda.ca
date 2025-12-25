<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => User::factory(),
            'service_provider_profile_id' => ServiceProviderProfile::factory(),
            'service_provider_id' => ServiceProvider::factory(),
            'booking_reference' => fake()->unique()->regexify('[A-Z]{3}[0-9]{6}'),
            'status' => fake()->randomElement(['pending', 'confirmed', 'in_progress', 'completed', 'cancelled']),
            'service_description' => fake()->paragraph(),
            'client_requirements' => fake()->optional()->paragraph(),
            'estimated_cost' => fake()->randomFloat(2, 50, 1000),
            'final_cost' => null,
            'preferred_date' => fake()->dateTimeBetween('+1 day', '+1 month'),
            'confirmed_date' => null,
            'completed_date' => null,
            'service_address' => fake()->address(),
            'client_phone' => fake()->phoneNumber(),
            'special_instructions' => fake()->optional()->sentence(),
            'payment_status' => fake()->randomElement(['pending', 'paid', 'refunded']),
            'payment_method' => fake()->randomElement(['cash', 'card', 'bank_transfer']),
            'service_provider_notes' => fake()->optional()->paragraph(),
            'client_feedback' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Create a pending booking.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Create a confirmed booking.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    /**
     * Create a completed booking.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'actual_cost' => fake()->randomFloat(2, 50, 500),
        ]);
    }

    /**
     * Create a cancelled booking.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Create for specific client.
     */
    public function forClient(int $clientId): static
    {
        return $this->state(fn (array $attributes) => [
            'client_id' => $clientId,
        ]);
    }

    /**
     * Create for specific service provider.
     */
    public function forServiceProvider(int $serviceProviderId): static
    {
        return $this->state(fn (array $attributes) => [
            'service_provider_id' => $serviceProviderId,
        ]);
    }
}
