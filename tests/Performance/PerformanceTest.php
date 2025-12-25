<?php

namespace Tests\Performance;

use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\Category;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * 🚀 Performance Tests
 *
 * Testing application performance, database queries, and optimization
 * Priority: ⭐⭐⭐ (Medium)
 */
class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed essential data
        $this->artisan('db:seed', ['--class' => 'CategorySeeder']);

        // Create test data for performance testing
        $this->createLargeDataset();
    }

    protected function createLargeDataset()
    {
        // Create 100 users
        $users = User::factory()->count(100)->create();

        // Create 50 service providers
        $category = Category::first();
        $providers = [];

        foreach ($users->take(50) as $user) {
            $user->update(['role' => 'service_provider']);
            $providers[] = ServiceProvider::factory()->create([
                'user_id' => $user->id,
                'category_id' => $category->id
            ]);
        }

        // Create 200 bookings
        $clients = $users->skip(50);
        foreach (range(1, 200) as $i) {
            Booking::factory()->create([
                'client_id' => $clients->random()->id,
                'service_provider_id' => collect($providers)->random()->id
            ]);
        }

        // Create 150 reviews
        $bookings = Booking::all();
        foreach (range(1, 150) as $i) {
            $booking = $bookings->random();
            Review::factory()->create([
                'booking_id' => $booking->id,
                'client_id' => $booking->client_id,
                'service_provider_id' => $booking->service_provider_id
            ]);
        }
    }

    /** @test */
    public function homepage_loads_within_performance_threshold()
    {
        $startTime = microtime(true);

        $response = $this->get('/');

        $loadTime = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(2.0, $loadTime, "Homepage took {$loadTime}s to load (should be < 2s)");
    }

    /** @test */
    public function service_provider_listing_performs_well()
    {
        $startTime = microtime(true);

        $response = $this->get('/service-providers');

        $loadTime = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(3.0, $loadTime, "Service provider listing took {$loadTime}s (should be < 3s)");
    }

    /** @test */
    public function search_functionality_is_optimized()
    {
        // Test search with various query complexities
        $searchTerms = [
            'mechanic',           // Simple term
            'auto repair service', // Multiple words
            'toro',               // Partial match
            'მექანიკოსი'           // Unicode
        ];

        foreach ($searchTerms as $term) {
            $startTime = microtime(true);

            $response = $this->get('/search?q=' . urlencode($term));

            $searchTime = microtime(true) - $startTime;

            $response->assertStatus(200);
            $this->assertLessThan(1.5, $searchTime,
                "Search for '{$term}' took {$searchTime}s (should be < 1.5s)");
        }
    }

    /** @test */
    public function database_queries_are_optimized()
    {
        DB::enableQueryLog();

        // Load service providers with relationships
        $providers = ServiceProvider::with(['user', 'category', 'reviews'])
                                  ->paginate(10);

        $queries = DB::getQueryLog();

        // Should not have N+1 query problem
        $this->assertLessThanOrEqual(5, count($queries),
            'Too many queries executed: ' . count($queries) . ' (should be ≤ 5)');

        DB::disableQueryLog();
    }

    /** @test */
    public function pagination_performance_is_consistent()
    {
        $pagesToTest = [1, 5, 10]; // Test different page numbers
        $loadTimes = [];

        foreach ($pagesToTest as $page) {
            $startTime = microtime(true);

            $response = $this->get("/service-providers?page={$page}");

            $loadTime = microtime(true) - $startTime;
            $loadTimes[] = $loadTime;

            $response->assertStatus(200);
            $this->assertLessThan(2.0, $loadTime,
                "Page {$page} took {$loadTime}s (should be < 2s)");
        }

        // Performance should be consistent across pages
        $avgLoadTime = array_sum($loadTimes) / count($loadTimes);
        $maxVariance = max($loadTimes) - min($loadTimes);

        $this->assertLessThan(1.0, $maxVariance,
            "Pagination performance varies too much: {$maxVariance}s variance");
    }

    /** @test */
    public function image_uploads_handle_large_files_efficiently()
    {
        $user = User::factory()->create(['role' => 'service_provider']);
        ServiceProvider::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

        // Create a 2MB test image
        $largeImage = \Illuminate\Http\UploadedFile::fake()
                     ->image('large_avatar.jpg')
                     ->size(2048); // 2MB

        $startTime = microtime(true);

        $response = $this->post('/profile/avatar', [
            'avatar' => $largeImage
        ]);

        $uploadTime = microtime(true) - $startTime;

        $this->assertLessThan(10.0, $uploadTime,
            "Large image upload took {$uploadTime}s (should be < 10s)");
    }

    /** @test */
    public function caching_improves_performance()
    {
        Cache::flush(); // Start with clean cache

        // First request (cache miss)
        $startTime = microtime(true);
        $response1 = $this->get('/service-providers');
        $firstCallTime = microtime(true) - $startTime;

        // Second request (should hit cache if implemented)
        $startTime = microtime(true);
        $response2 = $this->get('/service-providers');
        $secondCallTime = microtime(true) - $startTime;

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // If caching is implemented, second call should be faster
        // Note: This test might fail if caching is not implemented
        if ($firstCallTime > 0.1) { // Only test if first call was slow enough
            $this->assertLessThan($firstCallTime * 0.8, $secondCallTime,
                "Caching should improve performance. First: {$firstCallTime}s, Second: {$secondCallTime}s");
        }
    }

    /** @test */
    public function complex_dashboard_queries_perform_well()
    {
        $user = User::factory()->create(['role' => 'client']);
        $this->actingAs($user);

        // Create user's bookings
        Booking::factory()->count(20)->create(['client_id' => $user->id]);

        DB::enableQueryLog();
        $startTime = microtime(true);

        $response = $this->get('/dashboard');

        $loadTime = microtime(true) - $startTime;
        $queries = DB::getQueryLog();

        $response->assertStatus(200);
        $this->assertLessThan(2.0, $loadTime, "Dashboard took {$loadTime}s (should be < 2s)");
        $this->assertLessThanOrEqual(10, count($queries),
            'Dashboard uses too many queries: ' . count($queries));

        DB::disableQueryLog();
    }

    /** @test */
    public function api_endpoints_respond_quickly()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $apiEndpoints = [
            '/api/user',
            '/api/service-providers',
            '/api/categories'
        ];

        foreach ($apiEndpoints as $endpoint) {
            $startTime = microtime(true);

            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ])->get($endpoint);

            $responseTime = microtime(true) - $startTime;

            $this->assertLessThan(1.0, $responseTime,
                "API endpoint {$endpoint} took {$responseTime}s (should be < 1s)");
        }
    }

    /** @test */
    public function memory_usage_is_reasonable()
    {
        $initialMemory = memory_get_usage(true);

        // Load a page that processes significant data
        $response = $this->get('/service-providers');

        $finalMemory = memory_get_usage(true);
        $memoryUsed = $finalMemory - $initialMemory;

        $response->assertStatus(200);

        // Memory usage should be reasonable (less than 50MB)
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed,
            'Memory usage too high: ' . number_format($memoryUsed / 1024 / 1024, 2) . 'MB');
    }

    /** @test */
    public function concurrent_requests_handle_well()
    {
        // Simulate concurrent requests (limited simulation in unit tests)
        $startTime = microtime(true);

        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->get('/');
        }

        $totalTime = microtime(true) - $startTime;

        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        // All 5 requests should complete in reasonable time
        $this->assertLessThan(10.0, $totalTime,
            "5 concurrent requests took {$totalTime}s (should be < 10s)");
    }

    /** @test */
    public function large_result_sets_paginate_efficiently()
    {
        // Test pagination with large datasets
        $startTime = microtime(true);

        // Get first page
        $response = $this->get('/service-providers?per_page=50');

        $loadTime = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(3.0, $loadTime,
            "Large result set pagination took {$loadTime}s (should be < 3s)");

        // Verify pagination data is correct
        $data = $response->json();
        if (isset($data['data'])) {
            $this->assertLessThanOrEqual(50, count($data['data']));
        }
    }

    /** @test */
    public function database_indexes_improve_query_performance()
    {
        // Test queries that should benefit from indexes

        // Email lookup (should use index)
        $startTime = microtime(true);
        User::where('email', 'test@example.com')->first();
        $emailQueryTime = microtime(true) - $startTime;

        // Category lookup (should use index)
        $startTime = microtime(true);
        ServiceProvider::where('category_id', 1)->get();
        $categoryQueryTime = microtime(true) - $startTime;

        // Status lookup (should use index if implemented)
        $startTime = microtime(true);
        Booking::where('status', 'pending')->get();
        $statusQueryTime = microtime(true) - $startTime;

        // All indexed queries should be fast
        $this->assertLessThan(0.1, $emailQueryTime, "Email query too slow: {$emailQueryTime}s");
        $this->assertLessThan(0.1, $categoryQueryTime, "Category query too slow: {$categoryQueryTime}s");
        $this->assertLessThan(0.1, $statusQueryTime, "Status query too slow: {$statusQueryTime}s");
    }

    /** @test */
    public function file_serving_is_optimized()
    {
        // Test static file serving performance
        $startTime = microtime(true);

        $response = $this->get('/css/app.css');

        $loadTime = microtime(true) - $startTime;

        // CSS should load quickly
        $this->assertLessThan(1.0, $loadTime, "CSS file took {$loadTime}s (should be < 1s)");

        // Check for caching headers
        if ($response->getStatusCode() === 200) {
            // Should have cache-control headers for static assets
            $this->assertNotNull($response->headers->get('Cache-Control'));
        }
    }

    /** @test */
    public function session_handling_is_efficient()
    {
        $user = User::factory()->create();

        $startTime = microtime(true);

        // Login (creates session)
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $sessionTime = microtime(true) - $startTime;

        $this->assertLessThan(2.0, $sessionTime, "Session login took {$sessionTime}s (should be < 2s)");

        // Subsequent requests should be faster (session exists)
        $startTime = microtime(true);
        $this->get('/dashboard');
        $authenticatedTime = microtime(true) - $startTime;

        $this->assertLessThan(1.5, $authenticatedTime,
            "Authenticated request took {$authenticatedTime}s (should be < 1.5s)");
    }
}
