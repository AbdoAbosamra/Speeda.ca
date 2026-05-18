<?php

namespace Database\Factories;

use App\Models\ProviderAnalytics;
use App\Models\ServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProviderAnalyticsFactory extends Factory
{
    protected $model = ProviderAnalytics::class;

    public function definition(): array
    {
        return [
            'provider_id' => ServiceProvider::factory(),
            'action_type' => fake()->randomElement(['view', 'contact_reveal', 'whatsapp_click']),
            'session_hash' => Str::random(64),
            'created_at' => fake()->dateTimeBetween('-90 days', 'now'),
            'updated_at' => now(),
        ];
    }

    public function view(): static
    {
        return $this->state(fn() => ['action_type' => 'view']);
    }

    public function contactReveal(): static
    {
        return $this->state(fn() => ['action_type' => 'contact_reveal']);
    }

    public function whatsappClick(): static
    {
        return $this->state(fn() => ['action_type' => 'whatsapp_click']);
    }

    public function recent(): static
    {
        return $this->state(fn() => [
            'created_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function old(): static
    {
        return $this->state(fn() => [
            'created_at' => fake()->dateTimeBetween('-90 days', '-30 days'),
        ]);
    }

    public function forProvider(int $providerId): static
    {
        return $this->state(fn() => ['provider_id' => $providerId]);
    }

    public function withDate(string $date): static
    {
        return $this->state(fn() => ['created_at' => $date]);
    }
}
