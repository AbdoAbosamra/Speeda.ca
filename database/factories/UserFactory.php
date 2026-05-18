<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    private const ARABIC_FIRST_NAMES = [
        'محمد', 'أحمد', 'علي', 'عمر', 'خالد', 'يوسف', 'إبراهيم', 'عبدالله',
        'فاطمة', 'مريم', 'نور', 'سارة', 'ريم', 'لينا', 'دينا', 'هنا',
    ];

    private const ARABIC_LAST_NAMES = [
        'الأحمد', 'العلي', 'المحمد', 'الخالد', 'السيد', 'الحسن', 'العمر', 'الناصر',
    ];

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => fake()->randomElement(['client', 'service_provider']),
            'is_active' => true,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn() => ['email_verified_at' => null]);
    }

    public function client(): static
    {
        return $this->state(fn() => ['role' => 'client']);
    }

    public function serviceProvider(): static
    {
        return $this->state(fn() => ['role' => 'service_provider']);
    }

    public function admin(): static
    {
        return $this->state(fn() => ['role' => 'admin']);
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['is_active' => false]);
    }

    public function arabic(): static
    {
        $firstName = fake()->randomElement(self::ARABIC_FIRST_NAMES);
        $lastName = fake()->randomElement(self::ARABIC_LAST_NAMES);

        return $this->state(fn() => [
            'name' => "{$firstName} {$lastName}",
        ]);
    }

    public function withRole(string $role): static
    {
        return $this->state(fn() => ['role' => $role]);
    }

    public function withName(string $name): static
    {
        return $this->state(fn() => ['name' => $name]);
    }

    public function withEmail(string $email): static
    {
        return $this->state(fn() => ['email' => $email]);
    }

    public function testData(string $type = 'user', ?int $index = null): static
    {
        $index = $index ?? fake()->numberBetween(1, 9999);

        return $this->state(fn() => [
            'email' => "{$type}.{$index}." . Str::random(6) . "@test-speeda.ca",
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
    }
}
