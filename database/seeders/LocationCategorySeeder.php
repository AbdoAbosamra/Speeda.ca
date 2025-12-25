<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Location;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class LocationCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all locations and categories
        $locations = Location::all();
        $categories = Category::all();

        if ($locations->isEmpty() || $categories->isEmpty()) {
            $this->command->info('No locations or categories found. Please run LocationSeeder and CategorySeeder first.');
            return;
        }

        // Method 1: Attach random categories to each location
        foreach ($locations as $location) {
            // Attach 1-3 random categories to each location
            $randomCategories = $categories->random(rand(1, 3))->pluck('id')->toArray();
            $location->categories()->attach($randomCategories);
        }

        // Method 2: Create specific category assignments
        $montrealLocations = Location::where('city', 'Montreal')->get();
        $residentialCategory = Category::where('name', 'Residential')->first();
        $commercialCategory = Category::where('name', 'Commercial')->first();

        foreach ($montrealLocations as $location) {
            $location->categories()->attach([$residentialCategory->id, $commercialCategory->id]);
        }

        // Method 3: Using DB facade for specific assignments
        DB::table('location_category')->insert([
            [
                'location_id' => $locations->first()->id,
                'category_id' => $categories->where('name', 'Urban')->first()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'location_id' => $locations->get(1)->id,
                'category_id' => $categories->where('name', 'Suburban')->first()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('Location-Category relationships seeded successfully.');
    }
}
