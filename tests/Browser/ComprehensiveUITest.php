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
 * 🎨 Comprehensive UI & Browser Tests
 *
 * Complete browser testing suite covering all UI interactions, responsiveness,
 * accessibility, and cross-browser functionality
 * Priority: ⭐⭐⭐⭐⭐ (Critical)
 */
class ComprehensiveUITest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    #[Test]
    public function homepage_loads_correctly_and_is_responsive()
    {
        $this->browse(function (Browser $browser) {
            // Desktop view
            $browser->visit('/')
                   ->assertSee('Speeda')
                   ->assertSee('Find Service Providers')
                   ->assertVisible('.hero-section')
                   ->assertVisible('.search-form')
                   ->assertVisible('.categories-section');

            // Tablet view
            $browser->resize(768, 1024)
                   ->refresh()
                   ->assertVisible('.hero-section')
                   ->assertVisible('.mobile-nav-toggle');

            // Mobile view
            $browser->resize(375, 667)
                   ->refresh()
                   ->assertVisible('.mobile-nav-toggle')
                   ->click('.mobile-nav-toggle')
                   ->waitFor('.mobile-menu')
                   ->assertVisible('.mobile-menu');
        });
    }

    #[Test]
    public function navigation_works_across_all_pages()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(['role' => 'client']);

            $browser->loginAs($user)
                   ->visit('/')

                   // Test main navigation
                   ->clickLink('Service Providers')
                   ->waitForLocation('/service-providers')
                   ->assertSee('Service Providers')

                   ->clickLink('Categories')
                   ->waitForLocation('/categories')
                   ->assertSee('Categories')

                   ->clickLink('Locations')
                   ->waitForLocation('/locations')
                   ->assertSee('Locations')

                   ->clickLink('About Us')
                   ->waitForLocation('/about-us')
                   ->assertSee('About Us')

                   ->clickLink('Profile')
                   ->waitForLocation('/profile')
                   ->assertSee('Profile');
        });
    }

    #[Test]
    public function search_functionality_is_comprehensive()
    {
        $this->browse(function (Browser $browser) {
            // Create test data
            $category = Category::factory()->create(['name_en' => 'Auto Mechanics']);
            $location = Location::factory()->create(['city' => 'Toronto']);

            $serviceProvider = ServiceProvider::factory()->create([
                'category_id' => $category->id,
                'location_id' => $location->id,
                'business_name' => 'Quick Fix Auto',
                'services_offered' => 'Brake repair, Oil change'
            ]);

            $browser->visit('/service-providers')

                   // Test text search
                   ->type('search', 'brake')
                   ->press('Search')
                   ->waitFor('.service-provider-card')
                   ->assertSee('Quick Fix Auto')

                   // Test category filter
                   ->select('category_id', $category->id)
                   ->press('Filter')
                   ->waitFor('.service-provider-card')
                   ->assertSee('Quick Fix Auto')

                   // Test location filter
                   ->select('location_id', $location->id)
                   ->press('Filter')
                   ->waitFor('.service-provider-card')
                   ->assertSee('Quick Fix Auto')

                   // Test combined filters
                   ->type('search', 'oil')
                   ->press('Search')
                   ->waitFor('.service-provider-card')
                   ->assertSee('Quick Fix Auto')

                   // Clear filters
                   ->press('Clear Filters')
                   ->waitForReload()
                   ->assertInputValue('search', '');
        });
    }

    #[Test]
    public function user_registration_flow_is_complete()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                   ->assertSee('Register')

                   // Test client registration
                   ->type('name', 'John Client')
                   ->type('email', 'john@client.test')
                   ->type('password', 'SecurePass123!')
                   ->type('password_confirmation', 'SecurePass123!')
                   ->select('role', 'client')
                   ->press('Register')
                   ->waitForLocation('/service-providers')
                   ->assertAuthenticated();

            // Test service provider registration
            $browser->post('/logout')
                   ->visit('/register')
                   ->type('name', 'Jane Provider')
                   ->type('email', 'jane@provider.test')
                   ->type('password', 'SecurePass123!')
                   ->type('password_confirmation', 'SecurePass123!')
                   ->select('role', 'service_provider')
                   ->type('mobile_number', '+15145551234')
                   ->select('category_id', Category::first()->id)
                   ->select('location_id', Location::first()->id)
                   ->type('business_name', 'Jane Services')
                   ->press('Register')
                   ->waitForText('Profile')
                   ->assertAuthenticated();
        });
    }

    #[Test]
    public function login_and_logout_work_correctly()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create([
                'email' => 'test@login.test',
                'password' => bcrypt('password123')
            ]);

            $browser->visit('/register') // Login is in register page
                   ->click('#login-tab')
                   ->waitFor('#login-form')
                   ->type('email', 'test@login.test')
                   ->type('password', 'password123')
                   ->press('Login')
                   ->waitForLocation('/service-providers')
                   ->assertAuthenticated();

            // Test logout
            $browser->clickLink('Logout')
                   ->waitForLocation('/')
                   ->assertGuest();
        });
    }

    #[Test]
    public function service_provider_profile_is_fully_functional()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(['role' => 'service_provider']);
            $serviceProvider = ServiceProvider::factory()->create(['user_id' => $user->id]);

            $browser->loginAs($user)
                   ->visit('/service-providers/profile')
                   ->waitForLocation("/service-providers/{$serviceProvider->id}")

                   // Should see profile information
                   ->assertSee($serviceProvider->business_name)
                   ->assertSee($serviceProvider->services_offered)

                   // Test edit functionality (if available to owner)
                   ->assertVisible('.edit-profile-section')

                   // Test contact information reveal (if not owner)
                   ->logout()
                   ->visit("/service-providers/{$serviceProvider->id}")
                   ->assertSee('Reveal Contact Info')
                   ->click('.reveal-contact-btn')
                   ->waitFor('.contact-info')
                   ->assertVisible('.contact-info');
        });
    }

    #[Test]
    public function multilingual_interface_works_correctly()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')

                   // Default English
                   ->assertSee('Find Service Providers')
                   ->assertSee('Register')

                   // Switch to Arabic
                   ->click('.language-toggle')
                   ->clickLink('العربية')
                   ->waitForReload()
                   ->assertSee('ابحث عن مقدمي الخدمات')
                   ->assertSee('سجل')

                   // Switch to French
                   ->click('.language-toggle')
                   ->clickLink('Français')
                   ->waitForReload()
                   ->assertSee('Trouvez des fournisseurs de services')
                   ->assertSee('S\'inscrire')

                   // Back to English
                   ->click('.language-toggle')
                   ->clickLink('English')
                   ->waitForReload()
                   ->assertSee('Find Service Providers');
        });
    }

    #[Test]
    public function whatsapp_integration_works_properly()
    {
        $this->browse(function (Browser $browser) {
            $serviceProvider = ServiceProvider::factory()->create([
                'whatsapp_number' => '+15145551234'
            ]);

            $browser->visit("/service-providers/{$serviceProvider->id}")
                   ->assertSee('WhatsApp')
                   ->click('.whatsapp-btn')
                   ->pause(1000); // Allow WhatsApp link to process

            // Verify WhatsApp URL is correct
            $whatsappUrl = $browser->attribute('.whatsapp-btn', 'href');
            $this->assertStringContainsString('wa.me/15145551234', $whatsappUrl);
        });
    }

    #[Test]
    public function form_validation_works_in_real_time()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')

                   // Test email validation
                   ->type('email', 'invalid-email')
                   ->click('body') // Trigger blur event
                   ->waitFor('.email-error')
                   ->assertSee('Please enter a valid email')

                   // Fix email
                   ->type('email', 'valid@email.test')
                   ->click('body')
                   ->waitUntilMissing('.email-error')

                   // Test password confirmation
                   ->type('password', 'password123')
                   ->type('password_confirmation', 'different')
                   ->click('body')
                   ->waitFor('.password-error')
                   ->assertSee('Passwords do not match')

                   // Fix password
                   ->type('password_confirmation', 'password123')
                   ->click('body')
                   ->waitUntilMissing('.password-error');
        });
    }

    #[Test]
    public function accessibility_features_are_present()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')

                   // Check for accessibility attributes
                   ->assertAttribute('img', 'alt', '*')
                   ->assertAttribute('input[type="text"]', 'aria-label', '*')
                   ->assertAttribute('button', 'aria-label', '*')

                   // Test keyboard navigation
                   ->keys('body', '{tab}', '{tab}', '{tab}')
                   ->assertFocused('a, button, input')

                   // Check color contrast (visual test)
                   ->assertVisible('.high-contrast-mode')
                   ->click('.high-contrast-toggle')
                   ->waitFor('.high-contrast')
                   ->assertPresent('.high-contrast');
        });
    }

    #[Test]
    public function error_pages_display_correctly()
    {
        $this->browse(function (Browser $browser) {
            // Test 404 page
            $browser->visit('/nonexistent-page')
                   ->assertSee('Page Not Found')
                   ->assertSee('404')
                   ->assertSee('Go Home')
                   ->clickLink('Go Home')
                   ->waitForLocation('/');

            // Test service provider not found
            $browser->visit('/service-providers/999999')
                   ->assertSee('Service Provider Not Found')
                   ->assertSee('Go Back')
                   ->clickLink('Go Back')
                   ->waitForLocation('/service-providers');
        });
    }

    #[Test]
    public function performance_indicators_work()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')

                   // Check page load time
                   ->waitFor('.content-loaded', 5) // Should load within 5 seconds

                   // Check lazy loading
                   ->scrollIntoView('.lazy-load-section')
                   ->waitFor('.lazy-loaded-content')
                   ->assertVisible('.lazy-loaded-content');
        });
    }

    #[Test]
    public function social_media_integration_works()
    {
        $this->browse(function (Browser $browser) {
            $serviceProvider = ServiceProvider::factory()->create([
                'facebook_url' => 'https://facebook.com/test',
                'instagram_url' => 'https://instagram.com/test'
            ]);

            $browser->visit("/service-providers/{$serviceProvider->id}")
                   ->assertVisible('.social-media-links')
                   ->assertAttribute('.facebook-link', 'href', 'https://facebook.com/test')
                   ->assertAttribute('.instagram-link', 'href', 'https://instagram.com/test');
        });
    }

    #[Test]
    public function contact_reveal_system_works()
    {
        $this->browse(function (Browser $browser) {
            $serviceProvider = ServiceProvider::factory()->create([
                'phone_number' => '+15145551234',
                'whatsapp_number' => '+15145555678'
            ]);

            // Visit as guest
            $browser->visit("/service-providers/{$serviceProvider->id}")
                   ->assertSee('Reveal Contact Info')
                   ->assertDontSee('+15145551234')

                   // Click reveal
                   ->click('.reveal-contact-btn')
                   ->waitFor('.contact-info')
                   ->assertSee('+15145551234')
                   ->assertSee('+15145555678')
                   ->assertVisible('.whatsapp-btn');
        });
    }

    #[Test]
    public function breadcrumb_navigation_works()
    {
        $this->browse(function (Browser $browser) {
            $category = Category::factory()->create(['name_en' => 'Auto Services']);
            $serviceProvider = ServiceProvider::factory()->create(['category_id' => $category->id]);

            $browser->visit("/service-providers/{$serviceProvider->id}")
                   ->assertVisible('.breadcrumb')
                   ->assertSee('Home')
                   ->assertSee('Service Providers')
                   ->assertSee($serviceProvider->business_name)

                   ->clickLink('Service Providers')
                   ->waitForLocation('/service-providers')
                   ->assertVisible('.service-provider-card');
        });
    }

    #[Test]
    public function infinite_scroll_or_pagination_works()
    {
        $this->browse(function (Browser $browser) {
            // Create multiple service providers
            ServiceProvider::factory()->count(20)->create();

            $browser->visit('/service-providers')
                   ->assertVisible('.service-provider-card')

                   // If pagination
                   ->assertVisible('.pagination')
                   ->clickLink('2')
                   ->waitForReload()
                   ->assertQueryStringHas('page', '2')

                   // Or if infinite scroll
                   ->scrollToBottom()
                   ->waitFor('.loading-more')
                   ->waitFor('.service-provider-card:nth-child(21)');
        });
    }

    #[Test]
    public function filter_and_sort_combinations_work()
    {
        $this->browse(function (Browser $browser) {
            $category1 = Category::factory()->create(['name_en' => 'Auto']);
            $category2 = Category::factory()->create(['name_en' => 'Home']);

            ServiceProvider::factory()->create(['category_id' => $category1->id, 'business_name' => 'Auto Service A']);
            ServiceProvider::factory()->create(['category_id' => $category2->id, 'business_name' => 'Home Service B']);

            $browser->visit('/service-providers')

                   // Filter by category
                   ->select('category_id', $category1->id)
                   ->press('Filter')
                   ->waitFor('.service-provider-card')
                   ->assertSee('Auto Service A')
                   ->assertDontSee('Home Service B')

                   // Add sorting
                   ->select('sort', 'name_asc')
                   ->press('Sort')
                   ->waitForReload()

                   // Clear filters
                   ->press('Clear')
                   ->waitForReload()
                   ->assertSee('Auto Service A')
                   ->assertSee('Home Service B');
        });
    }

    #[Test]
    public function image_gallery_and_modal_work()
    {
        $this->browse(function (Browser $browser) {
            $serviceProvider = ServiceProvider::factory()->create([
                'profile_image' => 'test-image.jpg',
                'gallery_images' => json_encode(['img1.jpg', 'img2.jpg', 'img3.jpg'])
            ]);

            $browser->visit("/service-providers/{$serviceProvider->id}")
                   ->assertVisible('.profile-image')
                   ->assertVisible('.gallery-images')

                   // Click image to open modal
                   ->click('.gallery-image:first-child')
                   ->waitFor('.image-modal')
                   ->assertVisible('.image-modal')
                   ->assertVisible('.modal-image')

                   // Navigate through images
                   ->click('.next-image')
                   ->waitFor('.modal-image[src*="img2.jpg"]')

                   // Close modal
                   ->click('.modal-close')
                   ->waitUntilMissing('.image-modal');
        });
    }

    #[Test]
    public function rtl_layout_works_for_arabic()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                   ->click('.language-toggle')
                   ->clickLink('العربية')
                   ->waitForReload()

                   // Check RTL attributes
                   ->assertAttribute('html', 'dir', 'rtl')
                   ->assertAttribute('body', 'class', '*rtl*')

                   // Check Arabic text display
                   ->assertSee('مرحباً بكم في سبيدا')
                   ->assertVisible('.rtl-layout');
        });
    }
}
