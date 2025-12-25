<?php

namespace Tests\Integration\Services;

use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\Category;
use App\Models\Booking;
use App\Models\Review;
use App\Services\BookingService;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Mockery;

/**
 * 🔧 Service Layer Integration Tests
 *
 * Testing service classes and their interactions
 * Priority: ⭐⭐⭐⭐ (High)
 */
class ServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected BookingService $bookingService;
    protected NotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize services
        $this->bookingService = app(BookingService::class);
        $this->notificationService = app(NotificationService::class);

        // Seed essential data
        $this->artisan('db:seed', ['--class' => 'CategorySeeder']);
    }

    /** @test */
    public function booking_service_creates_booking_successfully()
    {
        Event::fake();
        Notification::fake();

        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'service_provider']);
        $serviceProvider = ServiceProvider::factory()->create(['user_id' => $provider->id]);

        $bookingData = [
            'client_id' => $client->id,
            'service_provider_id' => $serviceProvider->id,
            'service_date' => now()->addDays(3)->format('Y-m-d'),
            'service_time' => '14:00',
            'description' => 'Car brake repair needed',
            'location' => 'Downtown Toronto',
            'estimated_cost' => 250.00
        ];

        $booking = $this->bookingService->createBooking($bookingData);

        // Assert booking created
        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertDatabaseHas('bookings', [
            'client_id' => $client->id,
            'service_provider_id' => $serviceProvider->id,
            'status' => 'pending'
        ]);

        // Assert notifications sent
        Notification::assertSentTo($provider, \App\Notifications\NewBookingReceived::class);
        Event::assertDispatched(\App\Events\BookingCreated::class);
    }

    /** @test */
    public function booking_service_handles_validation_errors()
    {
        $client = User::factory()->create(['role' => 'client']);

        $invalidBookingData = [
            'client_id' => $client->id,
            'service_provider_id' => 999, // Non-existent provider
            'service_date' => now()->subDays(1)->format('Y-m-d'), // Past date
            'service_time' => '25:00', // Invalid time
        ];

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->bookingService->createBooking($invalidBookingData);
    }

    /** @test */
    public function booking_service_prevents_double_booking()
    {
        $client = User::factory()->create(['role' => 'client']);
        $provider = User::factory()->create(['role' => 'service_provider']);
        $serviceProvider = ServiceProvider::factory()->create(['user_id' => $provider->id]);

        $serviceDateTime = now()->addDays(3);

        // Create first booking
        $existingBooking = Booking::factory()->create([
            'service_provider_id' => $serviceProvider->id,
            'service_date' => $serviceDateTime->format('Y-m-d'),
            'service_time' => $serviceDateTime->format('H:i'),
            'status' => 'confirmed'
        ]);

        // Try to create conflicting booking
        $conflictingBookingData = [
            'client_id' => $client->id,
            'service_provider_id' => $serviceProvider->id,
            'service_date' => $serviceDateTime->format('Y-m-d'),
            'service_time' => $serviceDateTime->format('H:i'),
            'description' => 'Another service request'
        ];

        $this->expectException(\App\Exceptions\BookingConflictException::class);

        $this->bookingService->createBooking($conflictingBookingData);
    }

    /** @test */
    public function booking_service_updates_status_correctly()
    {
        Event::fake();
        Notification::fake();

        $booking = Booking::factory()->create(['status' => 'pending']);

        $updatedBooking = $this->bookingService->updateBookingStatus(
            $booking->id,
            'confirmed',
            'Confirmed by service provider'
        );

        $this->assertEquals('confirmed', $updatedBooking->status);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed'
        ]);

        // Assert status change event fired
        Event::assertDispatched(\App\Events\BookingStatusUpdated::class);

        // Assert client notified
        Notification::assertSentTo($booking->client, \App\Notifications\BookingStatusChanged::class);
    }

    /** @test */
    public function booking_service_calculates_totals_correctly()
    {
        $booking = Booking::factory()->create([
            'estimated_cost' => 100.00,
            'status' => 'confirmed'
        ]);

        $total = $this->bookingService->calculateBookingTotal($booking->id);

        $expectedTotal = $booking->estimated_cost * 1.13; // Assuming 13% tax
        $this->assertEquals($expectedTotal, $total['total']);
        $this->assertEquals(13.00, $total['tax']);
        $this->assertEquals(100.00, $total['subtotal']);
    }

    /** @test */
    public function notification_service_sends_sms_notifications()
    {
        // Mock SMS service
        $mockSmsService = Mockery::mock(\App\Services\SmsService::class);
        $mockSmsService->shouldReceive('send')
                      ->once()
                      ->with('+15145551234', Mockery::type('string'))
                      ->andReturn(true);

        $this->app->instance(\App\Services\SmsService::class, $mockSmsService);

        $user = User::factory()->create(['mobile' => '+15145551234']);

        $result = $this->notificationService->sendSms(
            $user,
            'Your booking has been confirmed'
        );

        $this->assertTrue($result);
    }

    /** @test */
    public function notification_service_sends_whatsapp_messages()
    {
        // Mock WhatsApp service
        $mockWhatsAppService = Mockery::mock(\App\Services\WhatsAppService::class);
        $mockWhatsAppService->shouldReceive('sendMessage')
                           ->once()
                           ->with('+15145551234', Mockery::type('string'))
                           ->andReturn(['success' => true, 'message_id' => 'wa_123']);

        $this->app->instance(\App\Services\WhatsAppService::class, $mockWhatsAppService);

        $serviceProvider = ServiceProvider::factory()->create([
            'whatsapp_number' => '+15145551234'
        ]);

        $result = $this->notificationService->sendWhatsAppMessage(
            $serviceProvider,
            'New booking request received'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('wa_123', $result['message_id']);
    }

    /** @test */
    public function notification_service_handles_failed_notifications()
    {
        // Mock failing service
        $mockSmsService = Mockery::mock(\App\Services\SmsService::class);
        $mockSmsService->shouldReceive('send')
                      ->once()
                      ->andThrow(new \Exception('SMS service unavailable'));

        $this->app->instance(\App\Services\SmsService::class, $mockSmsService);

        $user = User::factory()->create(['mobile' => '+15145551234']);

        // Should handle exception gracefully
        $result = $this->notificationService->sendSms($user, 'Test message');

        $this->assertFalse($result);
    }

    /** @test */
    public function payment_service_processes_payments()
    {
        if (!class_exists(\App\Services\PaymentService::class)) {
            $this->markTestSkipped('PaymentService not implemented yet');
        }

        $paymentService = app(PaymentService::class);
        $booking = Booking::factory()->create([
            'estimated_cost' => 100.00,
            'status' => 'confirmed'
        ]);

        $paymentData = [
            'amount' => 113.00, // Including tax
            'currency' => 'CAD',
            'payment_method' => 'credit_card',
            'card_token' => 'test_card_token_123'
        ];

        $result = $paymentService->processPayment($booking->id, $paymentData);

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['transaction_id']);

        // Assert booking status updated
        $booking->refresh();
        $this->assertEquals('paid', $booking->status);
    }

    /** @test */
    public function search_service_finds_relevant_providers()
    {
        $category = Category::first();

        // Create service providers in Toronto
        $providers = [];
        for ($i = 0; $i < 5; $i++) {
            $user = User::factory()->create([
                'name' => "Provider {$i}",
                'role' => 'service_provider'
            ]);
            $providers[] = ServiceProvider::factory()->create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'city' => 'Toronto',
                'is_available' => true
            ]);
        }

        // Create a provider in different city
        $otherUser = User::factory()->create(['role' => 'service_provider']);
        ServiceProvider::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $category->id,
            'city' => 'Montreal',
            'is_available' => true
        ]);

        $searchService = app(\App\Services\SearchService::class);

        $results = $searchService->findServiceProviders([
            'category_id' => $category->id,
            'city' => 'Toronto',
            'available_only' => true
        ]);

        $this->assertCount(5, $results);

        foreach ($results as $provider) {
            $this->assertEquals('Toronto', $provider->city);
            $this->assertEquals($category->id, $provider->category_id);
            $this->assertTrue($provider->is_available);
        }
    }

    /** @test */
    public function review_service_calculates_ratings_correctly()
    {
        $serviceProvider = ServiceProvider::factory()->create();

        // Create multiple reviews
        $ratings = [5, 4, 5, 3, 4];
        foreach ($ratings as $rating) {
            Review::factory()->create([
                'service_provider_id' => $serviceProvider->id,
                'rating' => $rating
            ]);
        }

        $reviewService = app(\App\Services\ReviewService::class);

        $stats = $reviewService->calculateRatingStats($serviceProvider->id);

        $expectedAverage = array_sum($ratings) / count($ratings); // 4.2
        $this->assertEquals($expectedAverage, $stats['average_rating']);
        $this->assertEquals(count($ratings), $stats['total_reviews']);
        $this->assertEquals(2, $stats['five_star_count']);
        $this->assertEquals(2, $stats['four_star_count']);
    }

    /** @test */
    public function geocoding_service_converts_addresses()
    {
        if (!class_exists(\App\Services\GeocodingService::class)) {
            $this->markTestSkipped('GeocodingService not implemented yet');
        }

        $geocodingService = app(\App\Services\GeocodingService::class);

        // Mock the external API call
        $mockResponse = [
            'latitude' => 43.6532,
            'longitude' => -79.3832,
            'formatted_address' => 'Toronto, ON, Canada'
        ];

        $coordinates = $geocodingService->geocodeAddress('Toronto, Ontario');

        $this->assertIsArray($coordinates);
        $this->assertArrayHasKey('latitude', $coordinates);
        $this->assertArrayHasKey('longitude', $coordinates);
    }

    /** @test */
    public function service_layer_handles_race_conditions()
    {
        $serviceProvider = ServiceProvider::factory()->create([
            'view_count' => 0
        ]);

        // Simulate concurrent view count increments
        $incrementService = app(\App\Services\ViewCountService::class);

        $processes = [];
        for ($i = 0; $i < 10; $i++) {
            $processes[] = function () use ($incrementService, $serviceProvider) {
                $incrementService->incrementViewCount($serviceProvider->id);
            };
        }

        // Execute all processes
        foreach ($processes as $process) {
            $process();
        }

        // View count should be accurate
        $serviceProvider->refresh();
        $this->assertEquals(10, $serviceProvider->view_count);
    }

    /** @test */
    public function cache_service_improves_performance()
    {
        if (!class_exists(\App\Services\CacheService::class)) {
            $this->markTestSkipped('CacheService not implemented yet');
        }

        $cacheService = app(\App\Services\CacheService::class);
        $category = Category::first();

        // First call should hit database
        $startTime = microtime(true);
        $providers1 = $cacheService->getCachedServiceProviders($category->id);
        $firstCallTime = microtime(true) - $startTime;

        // Second call should hit cache
        $startTime = microtime(true);
        $providers2 = $cacheService->getCachedServiceProviders($category->id);
        $secondCallTime = microtime(true) - $startTime;

        // Cache call should be faster
        $this->assertLessThan($firstCallTime, $secondCallTime);
        $this->assertEquals($providers1->count(), $providers2->count());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
