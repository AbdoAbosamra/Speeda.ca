<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Default password
            'remember_token' => Str::random(10),
            'role' => fake()->randomElement(['client', 'service_provider']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a client user.
     */
    public function client(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'client',
            'mobile' => null, // Clients don't need mobile
        ]);
    }

    /**
     * Create a service provider user.
     */
    public function serviceProvider(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'service_provider',
            'mobile' => $this->generateCanadianPhone(),
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
     * Create user with specific role.
     */
    public function withRole(string $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => $role,
        ]);
    }

    /**
     * Create user with specific name.
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    /**
     * Create user with specific email.
     */
    public function withEmail(string $email): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => $email,
        ]);
    }

    /**
     * Create a test user with @test-speeda.ca email domain.
     * This makes the user easy to identify and clean up later.
     */
    public function testData(string $type = 'user', ?int $index = null): static
    {
        $uniqueId = \Illuminate\Support\Str::random(6);
        $index = $index ?? fake()->numberBetween(1, 9999);

        return $this->state(fn (array $attributes) => [
            'email' => "{$type}.{$index}.{$uniqueId}@test-speeda.ca",
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
    }

    /**
     * Create a Canadian-localized service provider user.
     */
    public function canadianProvider(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'service_provider',
            'mobile' => $this->generateCanadianPhone(),
        ]);
    }

    /**
     * Create a Canadian-localized client user.
     */
    public function canadianClient(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'client',
        ]);
    }
}
