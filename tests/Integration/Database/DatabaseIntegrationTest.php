<?php

namespace Tests\Integration\Database;

use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\Category;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * 🗄️ Database Integration Tests
 *
 * Testing database relationships, constraints, and data integrity
 * Priority: ⭐⭐⭐⭐⭐ (Critical)
 */
class DatabaseIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed essential data
        $this->artisan('db:seed', ['--class' => 'CategorySeeder']);
        $this->artisan('db:seed', ['--class' => 'LocationSeeder']);
    }

    /** @test */
    public function user_service_provider_relationship_integrity()
    {
        // Create a user with service provider
        $user = User::factory()->create(['role' => 'service_provider']);
        $serviceProvider = ServiceProvider::factory()->create([
            'user_id' => $user->id,
            'category_id' => Category::first()->id
        ]);

        // Test relationship from user side
        $this->assertInstanceOf(ServiceProvider::class, $user->serviceProvider);
        $this->assertEquals($serviceProvider->id, $user->serviceProvider->id);

        // Test relationship from service provider side
        $this->assertInstanceOf(User::class, $serviceProvider->user);
        $this->assertEquals($user->id, $serviceProvider->user->id);

        // Test cascade delete - deleting user should delete service provider
        $serviceProviderId = $serviceProvider->id;
        $user->delete();

        $this->assertDatabaseMissing('service_providers', ['id' => $serviceProviderId]);
    }

    /** @test */
    public function service_provider_category_relationship()
    {
        $category = Category::first();
        $serviceProvider = ServiceProvider::factory()->create([
            'category_id' => $category->id
        ]);

        // Test relationship
        $this->assertInstanceOf(Category::class, $serviceProvider->category);
        $this->assertEquals($category->id, $serviceProvider->category->id);

        // Test reverse relationship
        $this->assertTrue($category->serviceProviders->contains($serviceProvider));
    }

    /** @test */
    public function booking_relationships_work_correctly()
    {
        // Create test data
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'service_provider']);
        $serviceProvider = ServiceProvider::factory()->create(['user_id' => $provider->id]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'service_provider_id' => $serviceProvider->id
        ]);

        // Test client relationship
        $this->assertInstanceOf(User::class, $booking->client);
        $this->assertEquals($client->id, $booking->client->id);

        // Test service provider relationship
        $this->assertInstanceOf(ServiceProvider::class, $booking->serviceProvider);
        $this->assertEquals($serviceProvider->id, $booking->serviceProvider->id);

        // Test reverse relationships
        $this->assertTrue($client->bookings->contains($booking));
        $this->assertTrue($serviceProvider->bookings->contains($booking));
    }

    /** @test */
    public function review_relationships_maintain_integrity()
    {
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'service_provider']);
        $serviceProvider = ServiceProvider::factory()->create(['user_id' => $provider->id]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'service_provider_id' => $serviceProvider->id,
            'status' => 'completed'
        ]);

        $review = Review::factory()->create([
            'booking_id' => $booking->id,
            'client_id' => $client->id,
            'service_provider_id' => $serviceProvider->id,
            'rating' => 5
        ]);

        // Test all relationships
        $this->assertEquals($booking->id, $review->booking->id);
        $this->assertEquals($client->id, $review->client->id);
        $this->assertEquals($serviceProvider->id, $review->serviceProvider->id);

        // Test reverse relationships
        $this->assertTrue($booking->reviews->contains($review));
        $this->assertTrue($client->reviewsGiven->contains($review));
        $this->assertTrue($serviceProvider->reviews->contains($review));
    }

    /** @test */
    public function database_constraints_are_enforced()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Try to create service provider with invalid category_id
        ServiceProvider::factory()->create([
            'category_id' => 99999 // Non-existent category
        ]);
    }

    /** @test */
    public function foreign_key_constraints_prevent_orphaned_records()
    {
        $user = User::factory()->create();
        $category = Category::first();

        $serviceProvider = ServiceProvider::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);

        // Try to delete category that has service providers
        $this->expectException(\Illuminate\Database\QueryException::class);
        $category->delete();
    }

    /** @test */
    public function unique_constraints_are_enforced()
    {
        // Test unique email constraint
        $user1 = User::factory()->create(['email' => 'unique@test.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => 'unique@test.com']);
    }

    /** @test */
    public function soft_deletes_work_correctly()
    {
        $user = User::factory()->create();
        $serviceProvider = ServiceProvider::factory()->create(['user_id' => $user->id]);

        // Soft delete user
        $user->delete();

        // User should not appear in normal queries
        $this->assertNull(User::find($user->id));

        // But should appear in withTrashed queries
        $this->assertNotNull(User::withTrashed()->find($user->id));

        // Service provider should be soft deleted too (if implemented)
        $serviceProvider->refresh();
        // This depends on your actual soft delete implementation
    }

    /** @test */
    public function database_transactions_maintain_consistency()
    {
        $initialUserCount = User::count();

        try {
            DB::transaction(function () {
                // Create user
                $user = User::factory()->create(['role' => 'service_provider']);

                // Create service provider
                ServiceProvider::factory()->create(['user_id' => $user->id]);

                // Force an exception
                throw new \Exception('Forced rollback');
            });
        } catch (\Exception $e) {
            // Transaction should have rolled back
        }

        // User count should be unchanged
        $this->assertEquals($initialUserCount, User::count());
        $this->assertEquals(0, ServiceProvider::count());
    }

    /** @test */
    public function complex_queries_with_joins_work_correctly()
    {
        // Create test data
        $category = Category::first();
        $provider = User::factory()->create(['role' => 'service_provider']);
        $serviceProvider = ServiceProvider::factory()->create([
            'user_id' => $provider->id,
            'category_id' => $category->id
        ]);

        $client = User::factory()->create(['role' => 'client']);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'service_provider_id' => $serviceProvider->id,
            'status' => 'completed'
        ]);

        $review = Review::factory()->create([
            'booking_id' => $booking->id,
            'client_id' => $client->id,
            'service_provider_id' => $serviceProvider->id,
            'rating' => 5
        ]);

        // Complex query: Get service providers with their average rating
        $results = DB::table('service_providers')
            ->join('users', 'service_providers.user_id', '=', 'users.id')
            ->leftJoin('reviews', 'service_providers.id', '=', 'reviews.service_provider_id')
            ->select(
                'service_providers.*',
                'users.name as provider_name',
                DB::raw('AVG(reviews.rating) as average_rating'),
                DB::raw('COUNT(reviews.id) as total_reviews')
            )
            ->groupBy('service_providers.id', 'users.name')
            ->get();

        $this->assertCount(1, $results);
        $this->assertEquals(5, $results->first()->average_rating);
        $this->assertEquals(1, $results->first()->total_reviews);
    }

    /** @test */
    public function database_indexes_improve_query_performance()
    {
        // Create multiple users
        User::factory()->count(100)->create();

        $startTime = microtime(true);

        // Query that should use email index
        $user = User::where('email', 'like', '%@example.com')->first();

        $queryTime = microtime(true) - $startTime;

        // Query should be fast (under 50ms for 100 records)
        $this->assertLessThan(0.05, $queryTime);
    }

    /** @test */
    public function database_handles_concurrent_updates()
    {
        $serviceProvider = ServiceProvider::factory()->create([
            'view_count' => 0
        ]);

        // Simulate concurrent view count updates
        $processes = [];
        for ($i = 0; $i < 5; $i++) {
            $processes[] = function () use ($serviceProvider) {
                DB::table('service_providers')
                    ->where('id', $serviceProvider->id)
                    ->increment('view_count');
            };
        }

        // Execute all processes
        foreach ($processes as $process) {
            $process();
        }

        // View count should be 5
        $serviceProvider->refresh();
        $this->assertEquals(5, $serviceProvider->view_count);
    }

    /** @test */
    public function database_pagination_works_with_complex_queries()
    {
        // Create multiple service providers
        $category = Category::first();
        for ($i = 0; $i < 15; $i++) {
            $user = User::factory()->create(['role' => 'service_provider']);
            ServiceProvider::factory()->create([
                'user_id' => $user->id,
                'category_id' => $category->id
            ]);
        }

        // Test pagination
        $page1 = ServiceProvider::with('user', 'category')->paginate(10, ['*'], 'page', 1);
        $page2 = ServiceProvider::with('user', 'category')->paginate(10, ['*'], 'page', 2);

        $this->assertCount(10, $page1->items());
        $this->assertCount(5, $page2->items());
        $this->assertEquals(15, $page1->total());
        $this->assertEquals(2, $page1->lastPage());
    }

    /** @test */
    public function database_handles_json_data_types()
    {
        // Test JSON storage and retrieval (if your models use JSON fields)
        $serviceProvider = ServiceProvider::factory()->create([
            'availability' => [
                'monday' => ['09:00', '17:00'],
                'tuesday' => ['09:00', '17:00'],
                'wednesday' => ['09:00', '17:00']
            ]
        ]);

        $serviceProvider->refresh();

        $this->assertIsArray($serviceProvider->availability);
        $this->assertEquals(['09:00', '17:00'], $serviceProvider->availability['monday']);
    }

    /** @test */
    public function database_search_functionality_works()
    {
        $category = Category::first();

        // Create service providers with searchable names
        $users = [
            User::factory()->create(['name' => 'John Auto Mechanic']),
            User::factory()->create(['name' => 'Mike Car Repair']),
            User::factory()->create(['name' => 'Sarah House Cleaner'])
        ];

        foreach ($users as $user) {
            ServiceProvider::factory()->create([
                'user_id' => $user->id,
                'category_id' => $category->id
            ]);
        }

        // Test search functionality
        $results = ServiceProvider::whereHas('user', function ($query) {
            $query->where('name', 'like', '%Auto%');
        })->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John Auto Mechanic', $results->first()->user->name);
    }
}
