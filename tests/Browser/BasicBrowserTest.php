<?php

namespace Tests\Browser;

use PHPUnit\Framework\Attributes\Test;

use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\Category;
use App\Models\Location;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * 🚀 Basic Browser Tests (Functional)
 *
 * Essential browser functionality tests that work without full Dusk setup
 * These tests simulate browser interactions and validate core functionality
 * Priority: ⭐⭐⭐⭐⭐ (Critical)
 */
class BasicBrowserTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    #[Test]
    public function homepage_navigation_simulation_works()
    {
        $this->browse(function ($browser) {
            $browser->visit('/')
                   ->assertSee('Speeda')
                   ->assertVisible('.hero-section')
                   ->click('.nav-link')
                   ->waitForLocation('/service-providers')
                   ->assertSee('Service Providers');
        });

        // Since we're using mock browser, let's also test with HTTP requests
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Speeda');
    }

    #[Test]
    public function user_registration_flow_simulation()
    {
        $this->browse(function ($browser) {
            $browser->visit('/register')
                   ->type('name', 'John Doe')
                   ->type('email', 'john@example.test')
                   ->type('password', 'SecurePass123!')
                   ->type('password_confirmation', 'SecurePass123!')
                   ->select('role', 'client')
                   ->press('Register')
                   ->waitForLocation('/service-providers')
                   ->assertSee('Service Providers');
        });

        // Verify with actual HTTP test
        $response = $this->post('/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'client'
        ]);

        $response->assertRedirect('/service-providers');
        $this->assertDatabaseHas('users', ['email' => 'jane@example.test']);
    }

    #[Test]
    public function service_provider_profile_access_simulation()
    {
        $serviceProvider = ServiceProvider::factory()->create([
            'business_name' => 'Test Business',
            'services_offered' => 'Web Development',
            'phone_number' => '+15145551234'
        ]);

        $this->browse(function ($browser) use ($serviceProvider) {
            $browser->visit("/service-providers/{$serviceProvider->id}")
                   ->assertSee('Test Business')
                   ->assertSee('Web Development')
                   ->click('.reveal-contact-btn')
                   ->waitFor('.contact-info')
                   ->assertSee('+15145551234');
        });

        // HTTP test
        $response = $this->get("/service-providers/{$serviceProvider->id}");
        $response->assertStatus(200);
        $response->assertSee('Test Business');
    }

    #[Test]
    public function search_functionality_simulation()
    {
        $category = Category::factory()->create(['name_en' => 'Web Development']);
        $serviceProvider = ServiceProvider::factory()->create([
            'category_id' => $category->id,
            'business_name' => 'DevCorp',
            'services_offered' => 'Laravel development'
        ]);

        $this->browse(function ($browser) {
            $browser->visit('/service-providers')
                   ->type('search', 'Laravel')
                   ->press('Search')
                   ->waitFor('.search-results')
                   ->assertSee('DevCorp');
        });

        // HTTP test
        $response = $this->get('/service-providers?search=Laravel');
        $response->assertStatus(200);
        $response->assertSee('DevCorp');
    }

    #[Test]
    public function mobile_responsive_simulation()
    {
        $this->browse(function ($browser) {
            $browser->resize(375, 667) // Mobile size
                   ->visit('/')
                   ->assertVisible('.mobile-nav-toggle')
                   ->click('.mobile-nav-toggle')
                   ->waitFor('.mobile-menu')
                   ->assertVisible('.mobile-menu');
        });

        // Test mobile user agent
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X)'
        ])->get('/');

        $response->assertStatus(200);
    }

    #[Test]
    public function multilingual_interface_simulation()
    {
        $this->browse(function ($browser) {
            $browser->visit('/')
                   ->assertSee('Find Service Providers')
                   ->click('.language-toggle')
                   ->clickLink('العربية')
                   ->waitForLocation('/')
                   ->assertSee('ابحث عن مقدمي الخدمات');
        });

        // HTTP test for language switching
        $response = $this->post('/locale', ['locale' => 'ar']);
        $response->assertRedirect();

        session(['locale' => 'ar']);
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    #[Test]
    public function contact_reveal_system_simulation()
    {
        $serviceProvider = ServiceProvider::factory()->create([
            'phone_number' => '+15145551234',
            'whatsapp_number' => '+15145555678'
        ]);

        $this->browse(function ($browser) use ($serviceProvider) {
            $browser->visit("/service-providers/{$serviceProvider->id}")
                   ->assertSee('Reveal Contact Info')
                   ->click('.reveal-contact-btn')
                   ->waitFor('.contact-info')
                   ->assertVisible('.whatsapp-btn')
                   ->assertSee('+15145551234');
        });

        // HTTP test for contact reveal
        $response = $this->post("/service-providers/{$serviceProvider->id}/reveal-contact");
        $response->assertStatus(200);
    }

    #[Test]
    public function form_validation_simulation()
    {
        $this->browse(function ($browser) {
            $browser->visit('/register')
                   ->press('Register')
                   ->waitFor('.error-message')
                   ->assertSee('The name field is required')
                   ->type('name', 'Test User')
                   ->type('email', 'invalid-email')
                   ->press('Register')
                   ->waitFor('.error-message')
                   ->assertSee('Please enter a valid email');
        });

        // HTTP validation test
        $response = $this->post('/register', []);
        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    #[Test]
    public function service_provider_dashboard_simulation()
    {
        $user = User::factory()->create(['role' => 'service_provider']);
        $serviceProvider = ServiceProvider::factory()->create(['user_id' => $user->id]);

        $this->browse(function ($browser) use ($user, $serviceProvider) {
            $browser->loginAs($user)
                   ->visit('/service-providers/profile')
                   ->waitForLocation("/service-providers/{$serviceProvider->id}")
                   ->assertSee($serviceProvider->business_name)
                   ->assertVisible('.edit-profile-section');
        });

        // HTTP test
        $response = $this->actingAs($user)->get('/service-providers/profile');
        $response->assertRedirect("/service-providers/{$serviceProvider->id}");
    }

    #[Test]
    public function error_handling_simulation()
    {
        $this->browse(function ($browser) {
            $browser->visit('/nonexistent-page')
                   ->assertSee('Page Not Found')
                   ->assertSee('404');
        });

        // HTTP test
        $response = $this->get('/nonexistent-page');
        $response->assertStatus(404);
    }

    #[Test]
    public function whatsapp_integration_simulation()
    {
        $serviceProvider = ServiceProvider::factory()->create([
            'whatsapp_number' => '+15145551234'
        ]);

        $this->browse(function ($browser) use ($serviceProvider) {
            $browser->visit("/service-providers/{$serviceProvider->id}")
                   ->click('.reveal-contact-btn')
                   ->waitFor('.whatsapp-btn')
                   ->assertVisible('.whatsapp-btn');
        });

        // Test WhatsApp URL generation
        $response = $this->get("/service-providers/{$serviceProvider->id}");
        $response->assertStatus(200);

        // Verify WhatsApp helper functionality
        $whatsappUrl = whatsapp_url('+15145551234', 'Hello from Speeda!');
        $this->assertStringContainsString('wa.me/15145551234', $whatsappUrl);
    }

    #[Test]
    public function category_filtering_simulation()
    {
        $category1 = Category::factory()->create(['name_en' => 'Auto Services']);
        $category2 = Category::factory()->create(['name_en' => 'Home Services']);

        ServiceProvider::factory()->create(['category_id' => $category1->id, 'business_name' => 'Auto Shop']);
        ServiceProvider::factory()->create(['category_id' => $category2->id, 'business_name' => 'Home Service']);

        $this->browse(function ($browser) use ($category1) {
            $browser->visit('/service-providers')
                   ->select('category_id', $category1->id)
                   ->press('Filter')
                   ->waitFor('.filtered-results')
                   ->assertSee('Auto Shop');
        });

        // HTTP test
        $response = $this->get("/service-providers?category_id={$category1->id}");
        $response->assertStatus(200);
        $response->assertSee('Auto Shop');
    }

    #[Test]
    public function authentication_flow_simulation()
    {
        $user = User::factory()->create([
            'email' => 'test@login.test',
            'password' => bcrypt('password123')
        ]);

        $this->browse(function ($browser) {
            $browser->visit('/register')
                   ->click('#login-tab')
                   ->waitFor('#login-form')
                   ->type('email', 'test@login.test')
                   ->type('password', 'password123')
                   ->press('Login')
                   ->waitForLocation('/service-providers');
        });

        // HTTP test
        $response = $this->post('/login', [
            'email' => 'test@login.test',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/service-providers');
        $this->assertAuthenticated();
    }
}
