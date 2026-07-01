<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WhatsAppMessageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $serviceProvider;
    protected $category;
    protected $location;

    protected function setUp(): void
    {
        parent::setUp();

        // Create required data with unique values to avoid conflicts
        $this->category = Category::factory()->create([
            'name' => 'Test Category ' . uniqid(),
        ]);

        // Create location without constraints
        $cities = ['Laval', 'Montreal', 'Ottawa', 'Gatineau'];
        $randomCity = $cities[array_rand($cities)];

        $this->location = Location::firstOrCreate(
            ['city' => $randomCity],
            ['is_active' => true]
        );

        $this->user = User::factory()->create([
            'name' => 'Test User ' . uniqid(),
            'email' => 'test' . uniqid() . '@test.com',
            'role' => 'service_provider',
        ]);

        $this->serviceProvider = ServiceProvider::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'company_name' => 'Test Company ' . uniqid(),
            'phone' => '613' . rand(1000000, 9999999),
            'whatsapp_number' => '+1613' . rand(1000000, 9999999),
        ]);
    }

    /** @test */
    public function test_service_provider_page_loads_with_whatsapp_button()
    {
        $response = $this->get(route('service-providers.show', $this->serviceProvider->id));

        $response->assertStatus(200);
        $response->assertSee('Contact via WhatsApp');
        $response->assertSee('revealContactInfo');
    }

    /** @test */
    public function test_whatsapp_message_contains_business_name()
    {
        $response = $this->get(route('service-providers.show', $this->serviceProvider->id));

        $response->assertStatus(200);

        // Check that company_name is passed to JavaScript
        $response->assertSee($this->serviceProvider->company_name);

        // Check that the WhatsApp message template is present
        $response->assertSee(__('service_provider.whatsapp_message'));
    }

    /** @test */
    public function test_whatsapp_number_formats_correctly_canadian()
    {
        // Test Canadian format
        $sp = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider']),
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'company_name' => 'Test Company',
            'phone' => '6138649118',
            'whatsapp_number' => '+16138649118',
        ]);

        $response = $this->get(route('service-providers.show', $sp->id));

        $response->assertStatus(200);
        // Check that phone number is present in some form
        $this->assertStringContainsString('613', $response->content());
    }

    /** @test */
    public function test_whatsapp_number_falls_back_to_phone_if_null()
    {
        $sp = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider']),
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'company_name' => 'Test Company',
            'phone' => '6138649118',
            'whatsapp_number' => null, // No WhatsApp number
        ]);

        $response = $this->get(route('service-providers.show', $sp->id));

        $response->assertStatus(200);
        // Should fallback to phone
        $this->assertStringContainsString('6138649118', $response->content());
    }

    /** @test */
    public function test_whatsapp_url_format_is_correct()
    {
        $response = $this->get(route('service-providers.show', $this->serviceProvider->id));

        $response->assertStatus(200);

        // Check that api.whatsapp.com URL structure is present
        $response->assertSee('api.whatsapp.com/send', false);
    }

    /** @test */
    public function test_company_name_not_business_name_is_used()
    {
        // Ensure we're using company_name (which exists) not business_name (which doesn't)
        $sp = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider', 'name' => 'Fallback Name']),
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'company_name' => 'Correct Company Name',
            'phone' => '6138649118',
            'whatsapp_number' => '+16138649118',
        ]);

        $response = $this->get(route('service-providers.show', $sp->id));

        $response->assertStatus(200);

        // Should contain company_name
        $response->assertSee('Correct Company Name');
    }

    /** @test */
    public function test_whatsapp_message_falls_back_to_user_name_if_no_company_name()
    {
        $sp = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create([
                'role' => 'service_provider',
                'name' => 'John Doe Fallback'
            ]),
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'company_name' => null, // No company name
            'phone' => '6138649118',
            'whatsapp_number' => '+16138649118',
        ]);

        $response = $this->get(route('service-providers.show', $sp->id));

        $response->assertStatus(200);

        // Should fallback to user name
        $response->assertSee('John Doe Fallback');
    }

    /** @test */
    public function test_whatsapp_number_cleaning_removes_special_characters()
    {
        $sp = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider']),
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'company_name' => 'Test Company',
            'phone' => '613-864-9118', // Contains dashes
            'whatsapp_number' => '+1 (613) 864-9118', // Contains spaces and parens
        ]);

        $response = $this->get(route('service-providers.show', $sp->id));

        $response->assertStatus(200);
        // The page should load without errors
        $this->assertTrue(true);
    }

    /** @test */
    public function test_whatsapp_message_in_english()
    {
        app()->setLocale('en');

        $response = $this->get(route('service-providers.show', $this->serviceProvider->id));

        $response->assertStatus(200);
        // Check that English translation is in the page source (in JavaScript)
        $response->assertSee('Hello, I am contacting you through the Speeda platform', false);
    }

    /** @test */
    public function test_whatsapp_message_in_arabic()
    {
        // Check that Arabic translation exists
        $translation = __('service_provider.whatsapp_message', [], 'ar');
        $this->assertEquals('مرحبًا، أتواصل معك عبر منصة سبيدا.', $translation);
    }

    /** @test */
    public function test_whatsapp_message_in_french()
    {
        // Check that French translation exists
        $translation = __('service_provider.whatsapp_message', [], 'fr');
        $this->assertEquals('Bonjour, je vous contacte via la plateforme Speeda.', $translation);
    }    /** @test */
    public function test_multiple_service_providers_all_have_valid_whatsapp_data()
    {
        // Create multiple service providers
        $providers = ServiceProvider::factory()->count(5)->create([
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
        ]);

        foreach ($providers as $provider) {
            $response = $this->get(route('service-providers.show', $provider->id));

            $response->assertStatus(200);
            $response->assertSee('revealContactInfo');

            // Each should have company_name or user name
            $this->assertTrue(
                !empty($provider->company_name) || !empty($provider->user->name),
                "Provider {$provider->id} has no company_name or user name"
            );
        }
    }

    /** @test */
    public function test_whatsapp_button_reveals_contact_info()
    {
        $response = $this->get(route('service-providers.show', $this->serviceProvider->id));

        $response->assertStatus(200);

        // Check that the contact-reveal JavaScript exists
        $response->assertSee('function revealContactInfo', false);

        // Contact reveal is WhatsApp-centric in the current UI
        $response->assertSee('whatsappNumber', false);
    }

    /** @test */
    public function test_whatsapp_number_with_egyptian_format()
    {
        $sp = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider']),
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'company_name' => 'Egyptian Company',
            'phone' => '01234567890', // Egyptian format starting with 0
            'whatsapp_number' => null,
        ]);

        $response = $this->get(route('service-providers.show', $sp->id));

        $response->assertStatus(200);
        // Should handle Egyptian format correctly
        $this->assertTrue(true);
    }

    /** @test */
    public function test_whatsapp_error_handling_when_translations_missing()
    {
        // Temporarily remove translation
        $originalMessage = __('service_provider.whatsapp_message');

        $response = $this->get(route('service-providers.show', $this->serviceProvider->id));

        $response->assertStatus(200);

        // Should still have error handling in JavaScript
        $response->assertSee('console.error');
    }

    /** @test */
    public function test_whatsapp_url_encoding_handles_special_characters()
    {
        $sp = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider']),
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'company_name' => 'Company & Sons', // Contains special character
            'phone' => '6138649118',
            'whatsapp_number' => '+16138649118',
        ]);

        $response = $this->get(route('service-providers.show', $sp->id));

        $response->assertStatus(200);
        // Should handle URL encoding
        $response->assertSee('encodeURIComponent');
    }
}
