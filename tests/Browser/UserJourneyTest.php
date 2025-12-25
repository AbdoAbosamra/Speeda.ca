<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\Category;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Helpers\TestHelper;

/**
 * 🌐 Browser User Journey Tests
 *
 * End-to-end user journey testing using Laravel Dusk
 * Priority: ⭐⭐⭐⭐ (High)
 */
class UserJourneyTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed essential data
        $this->artisan('db:seed', ['--class' => 'CategorySeeder']);
    }

    /** @test */
    public function client_can_complete_full_booking_journey()
    {
        $this->browse(function (Browser $browser) {
            $serviceProvider = TestHelper::createServiceProviderWithData();

            $browser->visit('/')
                   ->assertSee('Speeda') // Assuming this is your app name

                   // Register as client
                   ->clickLink('Register')
                   ->waitForText('Register')
                   ->type('name', 'John Client')
                   ->type('email', 'john@client.com')
                   ->type('password', 'SecurePass123!')
                   ->type('password_confirmation', 'SecurePass123!')
                   ->select('role', 'client')
                   ->press('Register')

                   // Should redirect to dashboard
                   ->waitForText('Dashboard')
                   ->assertPathIs('/dashboard')

                   // Search for service providers
                   ->clickLink('Find Service Provider')
                   ->waitForText('Service Providers')
                   ->type('search', 'mechanic')
                   ->press('Search')

                   // Select a service provider
                   ->waitFor('.service-provider-card')
                   ->click('.service-provider-card:first-child .btn-book')

                   // Fill booking form
                   ->waitForText('Book Service')
                   ->type('service_date', now()->addDays(3)->format('Y-m-d'))
                   ->select('service_time', '14:00')
                   ->type('description', 'Need brake repair for my car')
                   ->type('location', 'Downtown Toronto')
                   ->press('Book Now')

                   // Confirm booking created
                   ->waitForText('Booking Confirmed')
                   ->assertSee('Your booking has been submitted')

                   // Check booking appears in dashboard
                   ->visit('/dashboard')
                   ->waitForText('My Bookings')
                   ->assertSee('brake repair')
                   ->assertSee('Pending');
        });
    }

    /** @test */
    public function service_provider_can_manage_bookings()
    {
        $this->browse(function (Browser $browser) {
            // Create a service provider with a booking
            $serviceProvider = TestHelper::createServiceProviderWithData();
            $booking = TestHelper::createTestBooking([
                'service_provider_id' => $serviceProvider->id,
                'status' => 'pending'
            ]);

            $browser->visit('/login')
                   ->type('email', $serviceProvider->user->email)
                   ->type('password', 'password')
                   ->press('Login')

                   // Should redirect to service provider profile
                   ->waitForText('Service Provider Dashboard')
                   ->assertPathIs('/service-provider/profile')

                   // View pending bookings
                   ->clickLink('Bookings')
                   ->waitForText('Booking Requests')
                   ->assertSee($booking->description)
                   ->assertSee('Pending')

                   // Accept booking
                   ->press('Accept')
                   ->waitForText('Booking Confirmed')
                   ->assertSee('Booking has been confirmed')

                   // Verify status updated
                   ->refresh()
                   ->waitForText('Confirmed')
                   ->assertSee('Confirmed');
        });
    }

    /** @test */
    public function registration_form_validation_works_in_browser()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')

                   // Try to submit empty form
                   ->press('Register')
                   ->waitFor('.error-message')
                   ->assertSee('The name field is required')
                   ->assertSee('The email field is required')

                   // Try invalid email
                   ->type('name', 'Test User')
                   ->type('email', 'invalid-email')
                   ->type('password', 'password')
                   ->type('password_confirmation', 'password')
                   ->select('role', 'client')
                   ->press('Register')
                   ->waitFor('.error-message')
                   ->assertSee('Please enter a valid email')

                   // Fix email and try password mismatch
                   ->type('email', 'test@example.com')
                   ->type('password_confirmation', 'different-password')
                   ->press('Register')
                   ->waitFor('.error-message')
                   ->assertSee('password confirmation does not match')

                   // Fix password and submit successfully
                   ->type('password_confirmation', 'password')
                   ->press('Register')
                   ->waitForText('Dashboard')
                   ->assertPathIs('/dashboard');
        });
    }

    /** @test */
    public function mobile_responsive_design_works()
    {
        $this->browse(function (Browser $browser) {
            $user = TestHelper::createAuthenticatedUser();

            // Test mobile viewport
            $browser->resize(375, 667) // iPhone 6/7/8 size
                   ->visit('/login')
                   ->type('email', $user->email)
                   ->type('password', 'password123')
                   ->press('Login')

                   // Mobile menu should be visible
                   ->waitFor('.mobile-menu-toggle')
                   ->click('.mobile-menu-toggle')
                   ->waitFor('.mobile-nav')
                   ->assertVisible('.mobile-nav')

                   // Navigation should work on mobile
                   ->clickLink('Profile')
                   ->waitForText('Profile')

                   // Forms should be mobile-friendly
                   ->assertVisible('input[name="name"]')
                   ->assertDontSee('.desktop-only-element'); // If you have desktop-only elements
        });
    }

    /** @test */
    public function search_functionality_works_in_browser()
    {
        $this->browse(function (Browser $browser) {
            TestHelper::createSearchTestData();
            $user = TestHelper::createAuthenticatedUser();

            $browser->loginAs($user)
                   ->visit('/service-providers')

                   // Test search
                   ->type('search', 'mechanic')
                   ->press('Search')
                   ->waitFor('.service-provider-card')
                   ->assertSee('Auto Mechanics')

                   // Test filter by category
                   ->select('category', '1') // Auto mechanics category
                   ->press('Filter')
                   ->waitFor('.service-provider-card')

                   // Test location filter
                   ->type('city', 'Toronto')
                   ->press('Filter')
                   ->waitFor('.service-provider-card')

                   // Clear filters
                   ->press('Clear Filters')
                   ->waitForReload()
                   ->assertInputValue('search', '');
        });
    }

    /** @test */
    public function real_time_notifications_work()
    {
        if (!class_exists('Pusher\Pusher')) {
            $this->markTestSkipped('Real-time notifications require Pusher');
        }

        $this->browse(function (Browser $browser) {
            $client = TestHelper::createAuthenticatedUser('client');
            $serviceProvider = TestHelper::createServiceProviderWithData();

            // Login as service provider
            $browser->loginAs($serviceProvider->user)
                   ->visit('/service-provider/dashboard')

                   // In another browser, create a booking (simulated)
                   ->pause(1000) // Wait for WebSocket connection
                   ->assertDontSee('New Booking Request'); // Should not see notification initially

            // Simulate booking creation (this would normally trigger real notification)
            $booking = TestHelper::createTestBooking([
                'client_id' => $client->id,
                'service_provider_id' => $serviceProvider->id
            ]);

            $browser->waitForText('New Booking Request', 5) // Wait up to 5 seconds
                   ->assertSee('New Booking Request')
                   ->clickLink('View Booking')
                   ->waitForText($booking->description);
        });
    }

    /** @test */
    public function file_upload_works_in_browser()
    {
        $this->browse(function (Browser $browser) {
            $user = TestHelper::createAuthenticatedUser('service_provider');

            $browser->loginAs($user)
                   ->visit('/profile')

                   // Upload avatar
                   ->attach('avatar', __DIR__ . '/../fixtures/test-avatar.jpg')
                   ->press('Upload Avatar')
                   ->waitForText('Avatar updated successfully')
                   ->assertSee('Avatar updated')

                   // Verify image appears
                   ->waitFor('.profile-avatar')
                   ->assertVisible('.profile-avatar img');
        });
    }

    /** @test */
    public function language_switching_works()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')

                   // Should default to English
                   ->assertSee('Welcome')
                   ->assertSee('Register')

                   // Switch to Arabic
                   ->click('.language-selector')
                   ->clickLink('العربية')
                   ->waitForReload()
                   ->assertSee('مرحباً') // Welcome in Arabic
                   ->assertSee('تسجيل') // Register in Arabic

                   // Switch to French
                   ->click('.language-selector')
                   ->clickLink('Français')
                   ->waitForReload()
                   ->assertSee('Bienvenue') // Welcome in French
                   ->assertSee('S\'inscrire'); // Register in French
        });
    }

    /** @test */
    public function whatsapp_integration_launches_correctly()
    {
        $this->browse(function (Browser $browser) {
            $serviceProvider = TestHelper::createServiceProviderWithData([
                'service_provider' => ['whatsapp_number' => '+15145551234']
            ]);

            $browser->visit("/service-provider/{$serviceProvider->id}")
                   ->assertSee($serviceProvider->user->name)
                   ->assertSee('Contact via WhatsApp')

                   // Click WhatsApp button (should open WhatsApp web/app)
                   ->click('.whatsapp-button')
                   ->pause(2000); // Give time for WhatsApp to potentially open

            // Note: We can't easily test if WhatsApp actually opens in a browser test
            // But we can verify the link is correct
            $whatsappLink = $browser->attribute('.whatsapp-button', 'href');
            $this->assertStringContainsString('wa.me', $whatsappLink);
            $this->assertStringContainsString('15145551234', $whatsappLink);
        });
    }

    /** @test */
    public function booking_calendar_integration_works()
    {
        $this->browse(function (Browser $browser) {
            $serviceProvider = TestHelper::createServiceProviderWithData();
            $client = TestHelper::createAuthenticatedUser('client');

            $browser->loginAs($client)
                   ->visit("/service-provider/{$serviceProvider->id}/book")

                   // Calendar should be visible
                   ->waitFor('.calendar-widget')
                   ->assertVisible('.calendar-widget')

                   // Select a date
                   ->click('.calendar-day[data-date="' . now()->addDays(5)->format('Y-m-d') . '"]')
                   ->waitFor('.time-slots')
                   ->assertVisible('.time-slots')

                   // Select time slot
                   ->click('.time-slot[data-time="14:00"]')
                   ->assertVisible('.booking-form')

                   // Complete booking
                   ->type('description', 'Calendar test booking')
                   ->type('location', 'Test location')
                   ->press('Book Now')
                   ->waitForText('Booking Confirmed');
        });
    }

    /** @test */
    public function error_handling_displays_user_friendly_messages()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/service-provider/999999') // Non-existent provider
                   ->assertSee('Service Provider Not Found')
                   ->assertSee('The service provider you are looking for does not exist')

                   // Test 500 error (if we can simulate it)
                   ->visit('/trigger-error') // This route would need to exist for testing
                   ->assertSee('Something Went Wrong')
                   ->assertSee('We are working to fix this issue');
        });
    }
}
