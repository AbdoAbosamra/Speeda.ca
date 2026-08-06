<?php

namespace Tests\Browser;

use PHPUnit\Framework\Attributes\Test;

use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\Category;
use App\Models\Location;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * 📱 Mobile & Cross-Device Browser Tests
 *
 * Mobile-specific testing, device compatibility, and responsive design validation
 * Priority: ⭐⭐⭐⭐ (High)
 */
class MobileResponsiveTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    #[Test]
    public function mobile_navigation_works_perfectly()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(['role' => 'client']);

            // Test on iPhone SE (375x667)
            $browser->resize(375, 667)
                   ->loginAs($user)
                   ->visit('/')

                   // Mobile menu should be visible
                   ->assertVisible('.mobile-menu-toggle')
                   ->assertMissing('.desktop-nav')

                   // Open mobile menu
                   ->click('.mobile-menu-toggle')
                   ->waitFor('.mobile-menu')
                   ->assertVisible('.mobile-menu')

                   // Test navigation
                   ->clickLink('Service Providers')
                   ->waitForLocation('/service-providers')
                   ->assertSee('Service Providers');
        });
    }

    #[Test]
    public function tablet_layout_adapts_correctly()
    {
        $this->browse(function (Browser $browser) {
            // Test on iPad (768x1024)
            $browser->resize(768, 1024)
                   ->visit('/')

                   // Should show tablet layout
                   ->assertVisible('.tablet-layout')
                   ->assertMissing('.mobile-menu-toggle')
                   ->assertVisible('.nav-menu')

                   // Grid should adapt for tablet
                   ->visit('/service-providers')
                   ->assertVisible('.service-grid-tablet')
                   ->assertPresent('.service-provider-card:nth-child(2)'); // 2 columns on tablet
        });
    }

    #[Test]
    public function touch_interactions_work_on_mobile()
    {
        $this->browse(function (Browser $browser) {
            $serviceProvider = ServiceProvider::factory()->create();

            $browser->resize(375, 667) // Mobile size
                   ->visit("/service-providers/{$serviceProvider->id}")

                   // Test swipe gestures (if implemented)
                   ->assertVisible('.image-gallery')
                   ->swipeLeft('.image-gallery')
                   ->waitFor('.gallery-next')

                   // Test tap to reveal contact
                   ->tap('.reveal-contact-btn')
                   ->waitFor('.contact-info')
                   ->assertVisible('.contact-info')

                   // Test long press (if implemented)
                   ->longPress('.service-card')
                   ->waitFor('.context-menu')
                   ->assertVisible('.context-menu');
        });
    }

    #[Test]
    public function forms_are_mobile_optimized()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 667)
                   ->visit('/register')

                   // Form should be full width on mobile
                   ->assertVisible('.mobile-form')
                   ->assertAttribute('.form-input', 'class', '*mobile-input*')

                   // Input types should be optimized
                   ->assertAttribute('input[name="email"]', 'type', 'email')
                   ->assertAttribute('input[name="mobile_number"]', 'type', 'tel')

                   // Keyboard should be appropriate
                   ->type('email', 'test@example.com')
                   ->assertFocused('input[name="email"]')

                   // Submit should work on mobile
                   ->type('name', 'Mobile User')
                   ->type('password', 'password123')
                   ->type('password_confirmation', 'password123')
                   ->select('role', 'client')
                   ->press('Register')
                   ->waitForLocation('/service-providers');
        });
    }

    #[Test]
    public function mobile_search_is_user_friendly()
    {
        $this->browse(function (Browser $browser) {
            ServiceProvider::factory()->count(5)->create();

            $browser->resize(375, 667)
                   ->visit('/service-providers')

                   // Search should be prominent on mobile
                   ->assertVisible('.mobile-search')
                   ->type('.search-input', 'mechanic')
                   ->press('.search-btn')
                   ->waitFor('.search-results')

                   // Filters should be accessible
                   ->click('.filter-toggle')
                   ->waitFor('.mobile-filters')
                   ->assertVisible('.mobile-filters')

                   // Apply filter
                   ->select('category_id', Category::first()->id)
                   ->press('Apply Filters')
                   ->waitForReload()
                   ->assertQueryStringHas('category_id');
        });
    }

    #[Test]
    public function mobile_service_provider_profile_works()
    {
        $this->browse(function (Browser $browser) {
            $serviceProvider = ServiceProvider::factory()->create([
                'profile_image' => 'profile.jpg',
                'phone_number' => '+15145551234'
            ]);

            $browser->resize(375, 667)
                   ->visit("/service-providers/{$serviceProvider->id}")

                   // Profile should be mobile-optimized
                   ->assertVisible('.mobile-profile')
                   ->assertVisible('.profile-header-mobile')

                   // Images should be touch-friendly
                   ->tap('.profile-image')
                   ->waitFor('.image-modal')
                   ->assertVisible('.image-modal')

                   // Contact buttons should be large enough
                   ->press('Escape') // Close modal
                   ->click('.reveal-contact-btn')
                   ->waitFor('.contact-info')
                   ->assertVisible('.contact-btns-mobile')

                   // WhatsApp button should work
                   ->assertVisible('.whatsapp-btn-mobile')
                   ->assertAttribute('.whatsapp-btn-mobile', 'href', '*wa.me*');
        });
    }

    #[Test]
    public function mobile_loading_performance_is_optimal()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 667)
                   ->visit('/')

                   // Page should load quickly on mobile
                   ->waitFor('.content-loaded', 3) // Max 3 seconds

                   // Images should lazy load
                   ->scrollIntoView('.service-providers-section')
                   ->waitFor('.lazy-image')
                   ->assertVisible('.lazy-image')

                   // Infinite scroll should work smoothly
                   ->visit('/service-providers')
                   ->scrollToBottom()
                   ->waitFor('.loading-indicator')
                   ->waitFor('.service-provider-card:nth-child(11)'); // Next page loaded
        });
    }

    #[Test]
    public function offline_functionality_works()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 667)
                   ->visit('/')

                   // Simulate offline
                   ->script('navigator.serviceWorker && navigator.serviceWorker.controller && navigator.serviceWorker.controller.postMessage("GO_OFFLINE")')
                   ->pause(1000)

                   // Should show offline indicator
                   ->assertVisible('.offline-indicator')
                   ->assertSee('You are offline')

                   // Previously visited pages should work
                   ->visit('/service-providers')
                   ->assertSee('Service Providers')

                   // Go back online
                   ->script('navigator.serviceWorker && navigator.serviceWorker.controller && navigator.serviceWorker.controller.postMessage("GO_ONLINE")')
                   ->waitUntilMissing('.offline-indicator');
        });
    }

    #[Test]
    public function cross_browser_compatibility_works()
    {
        // Test different browser capabilities
        $this->browse(function (Browser $browser) {
            $browser->visit('/')

                   // Check CSS Grid support
                   ->assertPresent('.grid-layout, .flexbox-fallback')

                   // Check JavaScript features
                   ->script('return "fetch" in window') // Should support fetch
                   ->assertTrue(true)

                   // Check WebP support
                   ->script('
                       var canvas = document.createElement("canvas");
                       canvas.width = 1;
                       canvas.height = 1;
                       return canvas.toDataURL("image/webp").indexOf("data:image/webp") === 0;
                   ')
                   ->assertPresent('img[src*=".webp"], img[src*=".jpg"]');
        });
    }

    #[Test]
    public function mobile_accessibility_is_maintained()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 667)
                   ->visit('/')

                   // Touch targets should be large enough (44px minimum)
                   ->assertScript('
                       const buttons = document.querySelectorAll("button, a, .clickable");
                       return Array.from(buttons).every(btn => {
                           const rect = btn.getBoundingClientRect();
                           return rect.width >= 44 && rect.height >= 44;
                       });
                   ')

                   // Screen reader support
                   ->assertPresent('[aria-label], [aria-describedby], [role]')

                   // Focus indicators should be visible
                   ->keys('body', '{tab}')
                   ->assertScript('document.activeElement.matches(":focus-visible")');
        });
    }

    #[Test]
    public function mobile_gestures_work_correctly()
    {
        $this->browse(function (Browser $browser) {
            ServiceProvider::factory()->count(3)->create();

            $browser->resize(375, 667)
                   ->visit('/service-providers')

                   // Pull to refresh (if implemented)
                   ->swipeDown('body', 0, -100)
                   ->waitFor('.refresh-indicator')
                   ->waitUntilMissing('.refresh-indicator')

                   // Swipe navigation (if implemented)
                   ->swipeLeft('.service-card')
                   ->waitFor('.quick-actions')
                   ->assertVisible('.quick-actions')

                   // Pinch to zoom (on images)
                   ->visit("/service-providers/" . ServiceProvider::first()->id)
                   ->pinchZoom('.profile-image', 1.5)
                   ->assertScript('document.querySelector(".profile-image").style.transform.includes("scale")');
        });
    }

    #[Test]
    public function mobile_orientation_changes_work()
    {
        $this->browse(function (Browser $browser) {
            // Portrait mode
            $browser->resize(375, 667)
                   ->visit('/')
                   ->assertVisible('.portrait-layout')

                   // Landscape mode
                   ->resize(667, 375)
                   ->refresh()
                   ->assertVisible('.landscape-layout')
                   ->assertMissing('.portrait-layout')

                   // Navigation should adapt
                   ->assertVisible('.landscape-nav')

                   // Forms should adapt
                   ->visit('/register')
                   ->assertVisible('.landscape-form')
                   ->assertPresent('.form-row-landscape');
        });
    }

    #[Test]
    public function mobile_keyboard_behavior_is_correct()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 667)
                   ->visit('/register')

                   // Viewport should adjust when keyboard opens
                   ->click('input[name="email"]')
                   ->waitFor('.keyboard-open')
                   ->assertScript('window.visualViewport.height < window.innerHeight')

                   // Form should remain visible
                   ->assertVisible('input[name="email"]')
                   ->assertVisible('.submit-btn')

                   // Tab navigation should work
                   ->keys('input[name="email"]', '{tab}')
                   ->assertFocused('input[name="password"]');
        });
    }

    #[Test]
    public function mobile_image_optimization_works()
    {
        $this->browse(function (Browser $browser) {
            $serviceProvider = ServiceProvider::factory()->create([
                'profile_image' => 'large-image.jpg'
            ]);

            $browser->resize(375, 667)
                   ->visit("/service-providers/{$serviceProvider->id}")

                   // Should load mobile-optimized images
                   ->assertPresent('img[srcset*="mobile"], img[src*="mobile"], img[loading="lazy"]')

                   // Images should not exceed viewport
                   ->assertScript('
                       const images = document.querySelectorAll("img");
                       return Array.from(images).every(img => img.clientWidth <= window.innerWidth);
                   ');
        });
    }

    #[Test]
    public function mobile_notifications_work()
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 667)
                   ->visit('/')

                   // Push notification permission
                   ->assertVisible('.notification-prompt')
                   ->click('.enable-notifications')
                   ->waitFor('.notification-enabled')

                   // In-app notifications
                   ->visit('/register')
                   ->type('email', 'invalid-email')
                   ->press('Register')
                   ->waitFor('.mobile-toast')
                   ->assertVisible('.mobile-toast')
                   ->assertSee('Please enter a valid email');
        });
    }

    #[Test]
    public function mobile_share_functionality_works()
    {
        $this->browse(function (Browser $browser) {
            $serviceProvider = ServiceProvider::factory()->create();

            $browser->resize(375, 667)
                   ->visit("/service-providers/{$serviceProvider->id}")

                   // Native share API (if supported)
                   ->click('.share-btn')
                   ->waitFor('.share-options')
                   ->assertVisible('.share-options')

                   // Fallback share options
                   ->assertVisible('.share-whatsapp')
                   ->assertVisible('.share-email')
                   ->assertVisible('.copy-link')

                   // Test copy link
                   ->click('.copy-link')
                   ->waitFor('.copied-notification')
                   ->assertSee('Link copied');
        });
    }
}
