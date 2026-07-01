<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    private const CANADIAN_CITIES = [
        ['city' => 'Montreal',   'lat' => 45.5017, 'lng' => -73.5673],
        ['city' => 'Laval',      'lat' => 45.6066, 'lng' => -73.7124],
        ['city' => 'Longueuil',  'lat' => 45.5369, 'lng' => -73.5105],
        ['city' => 'Gatineau',   'lat' => 45.4763, 'lng' => -75.7016],
        ['city' => 'Ottawa',     'lat' => 45.4215, 'lng' => -75.6972],
        ['city' => 'Toronto',    'lat' => 43.6532, 'lng' => -79.3832],
        ['city' => 'Mississauga','lat' => 43.5890, 'lng' => -79.6441],
        ['city' => 'Brampton',   'lat' => 43.7315, 'lng' => -79.7624],
        ['city' => 'Vancouver',  'lat' => 49.2827, 'lng' => -123.1207],
        ['city' => 'Calgary',    'lat' => 51.0447, 'lng' => -114.0719],
        ['city' => 'Edmonton',   'lat' => 53.5461, 'lng' => -113.4938],
        ['city' => 'Quebec City','lat' => 46.8139, 'lng' => -71.2080],
    ];

    public function definition(): array
    {
        $cityData = fake()->randomElement(self::CANADIAN_CITIES);

        return [
            // Keep the city name unique so multiple factory-made locations never
            // collide on the unique `city` column. Explicit states (montreal(),
            // toronto(), withCity()) still set a fixed name when a test needs one.
            'city' => $cityData['city'] . ' ' . fake()->unique()->numberBetween(1, 999999),
            'is_active' => true,
            'country' => 'Canada',
            'latitude' => $cityData['lat'],
            'longitude' => $cityData['lng'],
        ];
    }

    public function montreal(): static
    {
        return $this->state(fn() => [
            'city' => 'Montreal', 'country' => 'Canada', 'latitude' => 45.5017, 'longitude' => -73.5673,
        ]);
    }

    public function toronto(): static
    {
        return $this->state(fn() => [
            'city' => 'Toronto', 'country' => 'Canada', 'latitude' => 43.6532, 'longitude' => -79.3832,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['is_active' => false]);
    }

    public function withCity(string $city, float $lat, float $lng): static
    {
        return $this->state(fn() => [
            'city' => $city, 'latitude' => $lat, 'longitude' => $lng,
        ]);
    }
}
