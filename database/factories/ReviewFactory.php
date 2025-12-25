<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_provider_id' => ServiceProvider::factory(),
            'user_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->optional(0.8)->paragraph(),
            'is_verified' => fake()->boolean(70),
        ];
    }

    /**
     * Create a 5-star review.
     */
    public function fiveStars(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 5,
            'comment' => fake()->randomElement([
                'Excellent service! Highly recommend.',
                'Outstanding work, very professional.',
                'Perfect job, will definitely use again.',
                'Amazing service, exceeded expectations.'
            ])
        ]);
    }

    /**
     * Create a 1-star review.
     */
    public function oneStar(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 1,
            'comment' => fake()->randomElement([
                'Very disappointing service.',
                'Poor quality work.',
                'Not satisfied with the results.',
                'Would not recommend.'
            ])
        ]);
    }

    /**
     * Create with specific rating.
     */
    public function withRating(int $rating): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => max(1, min(5, $rating)),
        ]);
    }

    /**
     * Create for specific booking.
     */
    public function forBooking(int $bookingId): static
    {
        return $this->state(fn (array $attributes) => [
            'booking_id' => $bookingId,
        ]);
    }

    /**
     * Create without comment.
     */
    public function withoutComment(): static
    {
        return $this->state(fn (array $attributes) => [
            'comment' => null,
        ]);
    }
}
