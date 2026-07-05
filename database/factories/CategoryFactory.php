<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Electrician', 'Plumber', 'Carpenter', 'Painter', 'Roofer',
            'HVAC Technician', 'Landscaper', 'Cleaner', 'Mechanic',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 999),
            'description' => fake()->sentence(),
            'icon' => fake()->randomElement(['fas fa-tools', 'fas fa-home', 'fas fa-bolt']),
            'color' => fake()->hexColor(),
            'is_active' => true,
            'is_section' => false,
            'sort_order' => fake()->numberBetween(1, 100),
            'parent_id' => null,
        ];
    }

    public function section(): static
    {
        return $this->state(fn(array $attrs) => [
            'name' => fake()->unique()->randomElement([
                'Construction', 'Home Maintenance', 'Professional Services',
                'Technology', 'Moving & Transport',
            ]),
            'is_section' => true,
            'parent_id' => null,
            'sort_order' => fake()->numberBetween(1, 10),
        ]);
    }

    public function group(int $sectionId): static
    {
        return $this->state(fn(array $attrs) => [
            'name' => fake()->unique()->randomElement([
                'Electrical Work', 'Plumbing', 'Carpentry', 'Painting',
                'HVAC', 'Roofing', 'Landscaping', 'Cleaning',
            ]),
            'is_section' => false,
            'parent_id' => $sectionId,
            'sort_order' => fake()->numberBetween(1, 50),
        ]);
    }

    public function profession(int $groupId): static
    {
        return $this->state(fn(array $attrs) => [
            'name' => fake()->unique()->randomElement([
                'Electrician', 'Plumber', 'Carpenter', 'Painter', 'Roofer',
                'HVAC Tech', 'Landscaper', 'House Cleaner',
                'Solar Installer', 'Smart Home Tech', 'Interior Painter',
            ]),
            'is_section' => false,
            'parent_id' => $groupId,
            'sort_order' => fake()->numberBetween(1, 100),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['is_active' => false]);
    }

    public function named(string $name): static
    {
        return $this->state(fn() => ['name' => $name, 'slug' => Str::slug($name)]);
    }
}
