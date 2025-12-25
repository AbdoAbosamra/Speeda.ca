<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\Category;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * 🔧 Advanced Browser Functionality Tests
 *
 * Advanced features testing including real-time updates, file uploads,
 * complex interactions, and edge cases
 * Priority: ⭐⭐⭐⭐ (High)
 */
class AdvancedFunctionalityTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    /** @test */
    public function file_upload_works_with_validation()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(['role' => 'service_provider']);
            $serviceProvider = ServiceProvider::factory()->create(['user_id' => $user->id]);

            $browser->loginAs($user)
                   ->visit('/service-providers/profile')

                   // Test invalid file upload
                   ->attach('profile_image', __DIR__ . '/../fixtures/test-document.pdf')
                   ->press('Upload Image')
                   ->waitFor('.error-message')
                   ->assertSee('Please upload a valid image')

                   // Test valid file upload
                   ->attach('profile_image', __DIR__ . '/../fixtures/test-image.jpg')
                   ->press('Upload Image')
                   ->waitFor('.success-message')
                   ->assertSee('Image uploaded successfully')

                   // Verify image appears
                   ->waitFor('.profile-image')
                   ->assertVisible('.profile-image img[src*="test-image"]');
        });
    }

    /** @test */
    public function multiple_file_uploads_work()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(['role' => 'service_provider']);
            $serviceProvider = ServiceProvider::factory()->create(['user_id' => $user->id]);

            $browser->loginAs($user)
                   ->visit('/service-providers/profile')

                   // Upload multiple images
                   ->attach('gallery_images[]', [
                       __DIR__ . '/../fixtures/gallery1.jpg',
                       __DIR__ . '/../fixtures/gallery2.jpg',
                       __DIR__ . '/../fixtures/gallery3.jpg'
                   ])
                   ->press('Upload Gallery')
                   ->waitFor('.success-message')
                   ->assertSee('Gallery updated successfully')

                   // Verify all images appear
                   ->waitFor('.gallery-images')
                   ->assertVisible('.gallery-image:nth-child(1)')
                   ->assertVisible('.gallery-image:nth-child(2)')
                   ->assertVisible('.gallery-image:nth-child(3)');
        });
    }

    /** @test */
    public function drag_and_drop_file_upload_works()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(['role' => 'service_provider']);

            $browser->loginAs($user)
                   ->visit('/service-providers/profile')

                   // Test drag and drop area
                   ->assertVisible('.drop-zone')
                   ->dragFile(__DIR__ . '/../fixtures/test-image.jpg', '.drop-zone')
                   ->waitFor('.file-preview')
                   ->assertVisible('.file-preview')

                   // Confirm upload
                   ->press('Upload Files')
                   ->waitFor('.upload-success')
                   ->assertSee('Files uploaded successfully');
        });
    }

    /** @test */
    public function real_time_search_suggestions_work()
    {
        $this->browse(function (Browser $browser) {
            // Create test data
            ServiceProvider::factory()->create(['business_name' => 'Quick Auto Repair']);
            ServiceProvider::factory()->create(['business_name' => 'Fast Home Service']);
            ServiceProvider::factory()->create(['business_name' => 'Quick Clean Solutions']);

            $browser->visit('/service-providers')

                   // Start typing
                   ->type('search', 'qu')
                   ->waitFor('.search-suggestions', 2)
                   ->assertVisible('.search-suggestions')
                   ->assertSee('Quick Auto Repair')
                   ->assertSee('Quick Clean Solutions')

                   // Select suggestion
                   ->click('.suggestion:first-child')
                   ->waitFor('.search-results')
                   ->assertInputValue('search', 'Quick Auto Repair')
                   ->assertSee('Quick Auto Repair');
        });
    }

    /** @test */
    public function infinite_scroll_works_smoothly()
    {
        $this->browse(function (Browser $browser) {
            // Create many service providers
            ServiceProvider::factory()->count(50)->create();

            $browser->visit('/service-providers')
                   ->assertVisible('.service-provider-card')

                   // Count initial items
                   ->assertScript('document.querySelectorAll(".service-provider-card").length >= 10')

                   // Scroll to trigger load more
                   ->scrollToBottom()
                   ->waitFor('.loading-more')
                   ->waitFor('.service-provider-card:nth-child(21)')

                   // Verify more items loaded
                   ->assertScript('document.querySelectorAll(".service-provider-card").length >= 20')

                   // Scroll again
                   ->scrollToBottom()
                   ->waitFor('.service-provider-card:nth-child(31)')
                   ->assertScript('document.querySelectorAll(".service-provider-card").length >= 30');
        });
    }

    /** @test */
    public function real_time_notifications_display()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(['role' => 'service_provider']);
            $serviceProvider = ServiceProvider::factory()->create(['user_id' => $user->id]);

            $browser->loginAs($user)
                   ->visit('/dashboard')

                   // Simulate notification
                   ->script('
                       window.Echo && window.Echo.private("user.' . $user->id . '")
                           .notification((notification) => {
                               showNotification(notification);
                           });
                   ')

                   // Trigger a notification (simulate booking)
                   ->script('
                       showNotification({
                           type: "booking_request",
                           message: "New booking request received",
                           data: { booking_id: 1 }
                       });
                   ')

                   ->waitFor('.notification-toast')
                   ->assertVisible('.notification-toast')
                   ->assertSee('New booking request received');
        });
    }

    /** @test */
    public function modal_interactions_work_correctly()
    {
        $this->browse(function (Browser $browser) {
            $serviceProvider = ServiceProvider::factory()->create();

            $browser->visit("/service-providers/{$serviceProvider->id}")

                   // Open contact modal
                   ->click('.contact-modal-btn')
                   ->waitFor('.contact-modal')
                   ->assertVisible('.contact-modal')

                   // Modal should be closeable
                   ->click('.modal-close')
                   ->waitUntilMissing('.contact-modal')

                   // Reopen and close with escape
                   ->click('.contact-modal-btn')
                   ->waitFor('.contact-modal')
                   ->keys('body', '{escape}')
                   ->waitUntilMissing('.contact-modal')

                   // Close by clicking outside
                   ->click('.contact-modal-btn')
                   ->waitFor('.contact-modal')
                   ->click('.modal-backdrop')
                   ->waitUntilMissing('.contact-modal');
        });
    }

    /** @test */
    public function image_gallery_with_zoom_works()
    {
        $this->browse(function (Browser $browser) {
            $serviceProvider = ServiceProvider::factory()->create([
                'gallery_images' => json_encode(['img1.jpg', 'img2.jpg', 'img3.jpg'])
            ]);

            $browser->visit("/service-providers/{$serviceProvider->id}")
                   ->assertVisible('.image-gallery')

                   // Open gallery modal
                   ->click('.gallery-image:first-child')
                   ->waitFor('.gallery-modal')
                   ->assertVisible('.gallery-modal')

                   // Navigate through images
                   ->click('.gallery-next')
                   ->waitFor('.gallery-image-2')
                   ->assertVisible('.gallery-image-2')

                   // Zoom functionality
                   ->click('.zoom-in')
                   ->assertScript('document.querySelector(".gallery-image").style.transform.includes("scale")')

                   // Zoom out
                   ->click('.zoom-out')
                   ->assertScript('!document.querySelector(".gallery-image").style.transform.includes("scale")')

                   // Thumbnail navigation
                   ->click('.thumbnail:nth-child(3)')
                   ->waitFor('.gallery-image-3')
                   ->assertVisible('.gallery-image-3');
        });
    }

    /** @test */
    public function advanced_filtering_combinations_work()
    {
        $this->browse(function (Browser $browser) {
            // Create test data with specific attributes
            $category1 = Category::factory()->create(['name_en' => 'Auto Services']);
            $category2 = Category::factory()->create(['name_en' => 'Home Services']);

            ServiceProvider::factory()->create([
                'category_id' => $category1->id,
                'hourly_rate' => 50,
                'is_available' => true,
                'business_name' => 'Premium Auto'
            ]);

            ServiceProvider::factory()->create([
                'category_id' => $category1->id,
                'hourly_rate' => 30,
                'is_available' => true,
                'business_name' => 'Budget Auto'
            ]);

            $browser->visit('/service-providers')

                   // Apply multiple filters
                   ->select('category_id', $category1->id)
                   ->type('min_price', '40')
                   ->type('max_price', '60')
                   ->check('available_only')
                   ->press('Apply Filters')
                   ->waitFor('.filtered-results')

                   // Should show only Premium Auto
                   ->assertSee('Premium Auto')
                   ->assertDontSee('Budget Auto')

                   // Remove price filter
                   ->clear('min_price')
                   ->clear('max_price')
                   ->press('Apply Filters')
                   ->waitFor('.filtered-results')

                   // Should show both
                   ->assertSee('Premium Auto')
                   ->assertSee('Budget Auto');
        });
    }

    /** @test */
    public function keyboard_navigation_works_throughout_app()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')

                   // Tab through main navigation
                   ->keys('body', '{tab}')
                   ->assertFocused('a, button, input')

                   // Continue tabbing
                   ->keys('body', '{tab}', '{tab}')
                   ->assertFocused('a, button, input')

                   // Enter should activate focused element
                   ->keys('body', '{enter}')
                   ->pause(1000)

                   // Arrow keys in dropdown (if applicable)
                   ->visit('/service-providers')
                   ->click('.category-dropdown')
                   ->waitFor('.dropdown-menu')
                   ->keys('body', '{down}', '{down}', '{enter}')
                   ->waitFor('.filtered-results');
        });
    }

    /** @test */
    public function auto_complete_and_suggestions_work()
    {
        $this->browse(function (Browser $browser) {
            ServiceProvider::factory()->create(['business_name' => 'Montreal Auto Repair']);
            ServiceProvider::factory()->create(['business_name' => 'Toronto Home Services']);

            $browser->visit('/service-providers')

                   // Location autocomplete
                   ->type('location', 'Mont')
                   ->waitFor('.location-suggestions')
                   ->assertVisible('.location-suggestions')
                   ->assertSee('Montreal')

                   // Select suggestion
                   ->click('.location-suggestion:first-child')
                   ->assertInputValue('location', 'Montreal')

                   // Service autocomplete
                   ->type('service', 'auto')
                   ->waitFor('.service-suggestions')
                   ->assertVisible('.service-suggestions')
                   ->assertSee('Auto Repair')

                   ->click('.service-suggestion:first-child')
                   ->assertInputValue('service', 'Auto Repair');
        });
    }

    /** @test */
    public function dynamic_form_fields_work()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')

                   // Service provider fields should appear when role is selected
                   ->select('role', 'service_provider')
                   ->waitFor('.service-provider-fields')
                   ->assertVisible('.service-provider-fields')
                   ->assertVisible('input[name="mobile_number"]')
                   ->assertVisible('select[name="category_id"]')

                   // Fields should hide when switching to client
                   ->select('role', 'client')
                   ->waitUntilMissing('.service-provider-fields')
                   ->assertMissing('input[name="mobile_number"]')

                   // Category selection should show subcategories
                   ->select('role', 'service_provider')
                   ->waitFor('select[name="category_id"]')
                   ->select('category_id', Category::first()->id)
                   ->waitFor('.subcategory-fields')
                   ->assertVisible('.subcategory-fields');
        });
    }

    /** @test */
    public function error_recovery_works_gracefully()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')

                   // Simulate network error
                   ->script('
                       fetch = () => Promise.reject(new Error("Network Error"));
                   ')

                   // Try to perform action that requires network
                   ->click('.load-more-btn')
                   ->waitFor('.error-message')
                   ->assertSee('Something went wrong')
                   ->assertVisible('.retry-btn')

                   // Retry should work
                   ->click('.retry-btn')
                   ->waitFor('.loading-indicator')

                   // Restore network and retry
                   ->refresh() // Reset page to restore fetch
                   ->click('.load-more-btn')
                   ->waitFor('.new-content')
                   ->assertVisible('.new-content');
        });
    }

    /** @test */
    public function progressive_web_app_features_work()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')

                   // PWA install prompt
                   ->assertPresent('link[rel="manifest"]')
                   ->assertPresent('.pwa-install-prompt')

                   // Service worker registration
                   ->assertScript('navigator.serviceWorker !== undefined')

                   // Offline indicator
                   ->script('window.dispatchEvent(new Event("offline"))')
                   ->waitFor('.offline-indicator')
                   ->assertVisible('.offline-indicator')

                   // Back online
                   ->script('window.dispatchEvent(new Event("online"))')
                   ->waitUntilMissing('.offline-indicator');
        });
    }

    /** @test */
    public function complex_user_interactions_work()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(['role' => 'client']);
            $serviceProvider = ServiceProvider::factory()->create();

            $browser->loginAs($user)
                   ->visit("/service-providers/{$serviceProvider->id}")

                   // Multi-step interaction
                   ->click('.reveal-contact-btn')
                   ->waitFor('.contact-info')
                   ->assertVisible('.contact-info')

                   // Copy phone number
                   ->click('.copy-phone-btn')
                   ->waitFor('.copied-indicator')
                   ->assertSee('Phone number copied')

                   // Open WhatsApp in new tab
                   ->rightClick('.whatsapp-btn') // Right-click for context menu
                   ->waitFor('.context-menu')
                   ->click('.open-in-new-tab')

                   // Save to favorites
                   ->click('.favorite-btn')
                   ->waitFor('.favorite-added')
                   ->assertSee('Added to favorites')

                   // Share provider
                   ->click('.share-btn')
                   ->waitFor('.share-modal')
                   ->click('.copy-link-btn')
                   ->waitFor('.link-copied')
                   ->assertSee('Link copied to clipboard');
        });
    }
}
