<?php

namespace Tests\Helpers;

use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\Category;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Facades\Hash;

/**
 * 🛠️ Test Helper Utilities
 *
 * Common test utilities and data creation helpers
 */
class TestHelper
{
    /**
     * Create a complete service provider with all relationships
     */
    public static function createServiceProviderWithData(array $overrides = []): ServiceProvider
    {
        $category = Category::first() ?? Category::factory()->create();

        $user = User::factory()->create(array_merge([
            'role' => 'service_provider'
        ], $overrides['user'] ?? []));

        $serviceProvider = ServiceProvider::factory()->create(array_merge([
            'user_id' => $user->id,
            'category_id' => $category->id
        ], $overrides['service_provider'] ?? []));

        return $serviceProvider;
    }

    /**
     * Create a client with bookings and reviews
     */
    public static function createClientWithHistory(int $bookingCount = 3, int $reviewCount = 2): User
    {
        $client = User::factory()->create(['role' => 'client']);

        $serviceProviders = [];
        for ($i = 0; $i < $bookingCount; $i++) {
            $serviceProviders[] = self::createServiceProviderWithData();
        }

        // Create bookings
        foreach ($serviceProviders as $index => $provider) {
            Booking::factory()->create([
                'client_id' => $client->id,
                'service_provider_id' => $provider->id,
                'status' => $index < $reviewCount ? 'completed' : 'pending'
            ]);
        }

        // Create reviews for completed bookings
        $completedBookings = Booking::where('client_id', $client->id)
                                  ->where('status', 'completed')
                                  ->get();

        foreach ($completedBookings as $booking) {
            Review::factory()->create([
                'booking_id' => $booking->id,
                'client_id' => $client->id,
                'service_provider_id' => $booking->service_provider_id
            ]);
        }

        return $client;
    }

    /**
     * Create test data for search functionality
     */
    public static function createSearchTestData(): array
    {
        $categories = [
            Category::factory()->create(['name' => 'Auto Mechanics']),
            Category::factory()->create(['name' => 'House Cleaning']),
            Category::factory()->create(['name' => 'Electricians'])
        ];

        $providers = [];
        $cities = ['Toronto', 'Montreal', 'Vancouver'];

        foreach ($categories as $categoryIndex => $category) {
            for ($i = 0; $i < 3; $i++) {
                $user = User::factory()->create([
                    'name' => $category->name . " Provider {$i}",
                    'role' => 'service_provider'
                ]);

                $providers[] = ServiceProvider::factory()->create([
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'city' => $cities[$categoryIndex],
                    'is_available' => $i < 2 // First 2 are available
                ]);
            }
        }

        return compact('categories', 'providers');
    }

    /**
     * Create performance test dataset
     */
    public static function createLargeDataset(int $userCount = 100, int $providerCount = 50): array
    {
        // Create categories
        $categories = Category::factory()->count(5)->create();

        // Create users
        $users = User::factory()->count($userCount)->create();

        // Create service providers
        $providers = [];
        foreach ($users->take($providerCount) as $user) {
            $user->update(['role' => 'service_provider']);
            $providers[] = ServiceProvider::factory()->create([
                'user_id' => $user->id,
                'category_id' => $categories->random()->id
            ]);
        }

        // Create bookings
        $clients = $users->skip($providerCount);
        $bookings = [];

        foreach (range(1, $userCount * 2) as $i) {
            $bookings[] = Booking::factory()->create([
                'client_id' => $clients->random()->id,
                'service_provider_id' => collect($providers)->random()->id
            ]);
        }

        // Create reviews
        foreach (collect($bookings)->random($userCount) as $booking) {
            Review::factory()->create([
                'booking_id' => $booking->id,
                'client_id' => $booking->client_id,
                'service_provider_id' => $booking->service_provider_id
            ]);
        }

        return compact('users', 'providers', 'bookings', 'categories');
    }

    /**
     * Assert response time is within threshold
     */
    public static function assertResponseTime(callable $callback, float $maxSeconds, string $message = '')
    {
        $startTime = microtime(true);
        $result = $callback();
        $endTime = microtime(true);

        $actualTime = $endTime - $startTime;

        if ($actualTime > $maxSeconds) {
            $message = $message ?: "Operation took {$actualTime}s, expected < {$maxSeconds}s";
            throw new \PHPUnit\Framework\AssertionFailedError($message);
        }

        return $result;
    }

    /**
     * Create authenticated user for testing
     */
    public static function createAuthenticatedUser(string $role = 'client', array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => $role,
            'password' => Hash::make('password123')
        ], $attributes));

        if ($role === 'service_provider') {
            ServiceProvider::factory()->create(['user_id' => $user->id]);
        }

        return $user;
    }

    /**
     * Create test booking with complete data
     */
    public static function createTestBooking(array $overrides = []): Booking
    {
        $client = User::factory()->create(['role' => 'client']);
        $serviceProvider = self::createServiceProviderWithData();

        return Booking::factory()->create(array_merge([
            'client_id' => $client->id,
            'service_provider_id' => $serviceProvider->id
        ], $overrides));
    }

    /**
     * Generate test phone numbers in various formats
     */
    public static function getTestPhoneNumbers(): array
    {
        return [
            'valid' => [
                '5145551234',
                '(514) 555-1234',
                '514-555-1234',
                '514.555.1234',
                '+15145551234',
                '15145551234'
            ],
            'invalid' => [
                '123',                    // Too short
                '01234567890',           // Starts with 0
                '11234567890',           // Starts with 1
                '1234567890123456',      // Too long
                'not-a-phone-number',    // Non-numeric
                '555-0123'               // Missing area code
            ]
        ];
    }

    /**
     * Generate test email addresses
     */
    public static function getTestEmails(): array
    {
        return [
            'valid' => [
                'test@example.com',
                'user.name@domain.co.uk',
                'user+tag@domain.com',
                'firstname-lastname@domain-name.com'
            ],
            'invalid' => [
                'invalid-email',
                'invalid@',
                '@invalid.com',
                'invalid..email@test.com',
                'invalid@.com',
                'invalid@test.',
                ''
            ]
        ];
    }

    /**
     * Create test files for upload testing
     */
    public static function createTestFiles(): array
    {
        return [
            'valid_image' => \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg', 300, 300),
            'large_image' => \Illuminate\Http\UploadedFile::fake()->image('large.jpg', 2000, 2000)->size(2048),
            'invalid_type' => \Illuminate\Http\UploadedFile::fake()->create('script.php', 100, 'application/x-php'),
            'pdf_file' => \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 500, 'application/pdf'),
            'oversized' => \Illuminate\Http\UploadedFile::fake()->image('huge.jpg')->size(10240) // 10MB
        ];
    }

    /**
     * Mock external service calls
     */
    public static function mockExternalServices(): void
    {
        // Mock SMS service
        if (class_exists('\App\Services\SmsService')) {
            $mockSms = \Mockery::mock('\App\Services\SmsService');
            $mockSms->shouldReceive('send')->andReturn(true);
            app()->instance('\App\Services\SmsService', $mockSms);
        }

        // Mock WhatsApp service
        if (class_exists('\App\Services\WhatsAppService')) {
            $mockWhatsApp = \Mockery::mock('\App\Services\WhatsAppService');
            $mockWhatsApp->shouldReceive('sendMessage')->andReturn(['success' => true]);
            app()->instance('\App\Services\WhatsAppService', $mockWhatsApp);
        }

        // Mock payment service
        if (class_exists('\App\Services\PaymentService')) {
            $mockPayment = \Mockery::mock('\App\Services\PaymentService');
            $mockPayment->shouldReceive('processPayment')->andReturn(['success' => true, 'transaction_id' => 'test_123']);
            app()->instance('\App\Services\PaymentService', $mockPayment);
        }
    }

    /**
     * Clear all caches for testing
     */
    public static function clearAllCaches(): void
    {
        \Illuminate\Support\Facades\Cache::flush();
        \Illuminate\Support\Facades\Route::clearResolvedInstances();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    /**
     * Get memory usage in human readable format
     */
    public static function getMemoryUsage(): string
    {
        $bytes = memory_get_usage(true);
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Generate test data for translation testing
     */
    public static function getTranslationTestData(): array
    {
        return [
            'en' => [
                'welcome' => 'Welcome',
                'hello_name' => 'Hello :name',
                'car_mechanics' => 'Car Mechanics'
            ],
            'ar' => [
                'welcome' => 'مرحباً',
                'hello_name' => 'مرحباً :name',
                'car_mechanics' => 'ميكانيكي السيارات'
            ],
            'fr' => [
                'welcome' => 'Bienvenue',
                'hello_name' => 'Bonjour :name',
                'car_mechanics' => 'Mécaniciens automobiles'
            ]
        ];
    }

    /**
     * Assert database query count is optimized
     */
    public static function assertQueryCount(callable $callback, int $maxQueries, string $message = '')
    {
        \Illuminate\Support\Facades\DB::enableQueryLog();

        $result = $callback();

        $queries = \Illuminate\Support\Facades\DB::getQueryLog();
        $queryCount = count($queries);

        \Illuminate\Support\Facades\DB::disableQueryLog();

        if ($queryCount > $maxQueries) {
            $message = $message ?: "Expected max {$maxQueries} queries, but {$queryCount} were executed";
            throw new \PHPUnit\Framework\AssertionFailedError($message);
        }

        return $result;
    }
}
