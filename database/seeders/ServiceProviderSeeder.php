<?php

namespace Database\Seeders;

use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Location;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ServiceProviderSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating basic categories and locations...');

        // Create basic categories
        $categories = [
            ['name' => 'Plumbing', 'slug' => 'plumbing', 'is_active' => true],
            ['name' => 'Electrical', 'slug' => 'electrical', 'is_active' => true],
            ['name' => 'Cleaning', 'slug' => 'cleaning', 'is_active' => true],
            ['name' => 'Painting', 'slug' => 'painting', 'is_active' => true],
            ['name' => 'Carpentry', 'slug' => 'carpentry', 'is_active' => true],
            ['name' => 'Automotive', 'slug' => 'automotive', 'is_active' => true],
            ['name' => 'HVAC', 'slug' => 'hvac', 'is_active' => true],
            ['name' => 'Landscaping', 'slug' => 'landscaping', 'is_active' => true],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }

        // Create basic locations
        $locations = [
            ['name' => 'Cairo', 'slug' => 'cairo', 'is_active' => true],
            ['name' => 'Alexandria', 'slug' => 'alexandria', 'is_active' => true],
            ['name' => 'Giza', 'slug' => 'giza', 'is_active' => true],
            ['name' => 'Luxor', 'slug' => 'luxor', 'is_active' => true],
        ];

        foreach ($locations as $locationData) {
            Location::firstOrCreate(
                ['slug' => $locationData['slug']],
                $locationData
            );
        }

        $this->command->info('Creating 20 service providers...');

        // Create 20 service providers
        ServiceProvider::factory()->count(20)->create();

        $this->showStatistics();
    }

    private function showStatistics()
    {
        $total = ServiceProvider::count();
        $verified = ServiceProvider::where('is_verified', true)->count();
        $certified = ServiceProvider::where('is_cirtified', true)->count();

        $avgRating = ServiceProvider::avg('average_rating');
        $avgExperience = ServiceProvider::avg('experience_years');
        $totalReviews = ServiceProvider::sum('total_reviews');
        $totalJobs = ServiceProvider::sum('completed_jobs');

        $this->command->info('=== Service Provider Statistics ===');
        $this->command->info("Total Providers: {$total}");
        $this->command->info("Verified: {$verified}");
        $this->command->info("Certified: {$certified}");
        $this->command->info("Average Rating: " . number_format($avgRating, 2));
        $this->command->info("Average Experience: " . number_format($avgExperience, 1) . " years");
        $this->command->info("Total Reviews: {$totalReviews}");
        $this->command->info("Completed Jobs: {$totalJobs}");

        // Show sample providers
        $this->command->info("\n=== Sample Providers ===");
        ServiceProvider::with(['category', 'location', 'user'])
            ->take(5)
            ->get()
            ->each(function ($provider) {
                $this->command->info("🔧 {$provider->profession}");
                $this->command->info("   👤 {$provider->user->name}");
                $this->command->info("   📍 {$provider->location->name}");
                $this->command->info("   📁 {$provider->category->name}");
                $this->command->info("   ⭐ Rating: {$provider->average_rating}");
                $this->command->info("   🏢 Type: {$provider->business_type}");
                $this->command->info("   📞 {$provider->phone}");
                $this->command->info("   ---");
            });
    }
}
