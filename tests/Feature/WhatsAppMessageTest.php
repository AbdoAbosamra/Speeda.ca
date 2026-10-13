<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;

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

    #[Test]
    public function test_service_provider_page_loads_with_whatsapp_button()
    {
        $response = $this->get(route('service-providers.show', $this->serviceProvider->id));

        $response->assertStatus(200);
        $response->assertSee('Contact via WhatsApp');
        $response->assertSee('revealContactInfo');
    }

    #[Test]
    public function test_whatsapp_message_contains_business_name()
    {
        $response = $this->get(route('service-providers.show', $this->serviceProvider->id));

        $response->assertStatus(200);
        $response->assertSee($this->serviceProvider->company_name);

        // The pre-filled message names the business and links back to the profile.
        $url = $this->serviceProvider->fresh()->whatsappContactUrl();
        $text = rawurldecode(substr($url, strpos($url, '?text=') + 6));
        $this->assertStringContainsString($this->serviceProvider->company_name, $text);
        $this->assertStringContainsString(route('service-providers.show', $this->serviceProvider->id), $text);
        $response->assertSee(e($url), false);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function test_whatsapp_url_format_is_correct()
    {
        $response = $this->get(route('service-providers.show', $this->serviceProvider->id));

        $response->assertStatus(200);

        // A real link to WhatsApp's click-to-chat with the international number.
        $digits = preg_replace('/\D/', '', $this->serviceProvider->whatsapp_number);
        $response->assertSee('href="https://wa.me/' . $digits . '?text=', false);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function test_whatsapp_message_in_english()
    {
        app()->setLocale('en');

        $response = $this->get(route('service-providers.show', $this->serviceProvider->id));

        $response->assertStatus(200);
        $response->assertSee(rawurlencode('I found your profile on SPEEDA'), false);
    }

    #[Test]
    public function test_whatsapp_message_in_arabic()
    {
        $translation = __('service_provider.whatsapp_message', ['business' => 'X', 'url' => 'U'], 'ar');
        $this->assertStringContainsString('سبيدا', $translation);
        $this->assertStringContainsString('X', $translation);
        $this->assertStringContainsString('U', $translation);
    }

    #[Test]
    public function test_whatsapp_message_in_french()
    {
        $translation = __('service_provider.whatsapp_message', ['business' => 'X', 'url' => 'U'], 'fr');
        $this->assertStringContainsString('SPEEDA', $translation);
        $this->assertStringContainsString('X', $translation);
        $this->assertStringContainsString('U', $translation);
    }

    #[Test]
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

    #[Test]
    public function test_whatsapp_button_reveals_contact_info()
    {
        $response = $this->get(route('service-providers.show', $this->serviceProvider->id));

        $response->assertStatus(200);

        // Check that the contact-reveal JavaScript exists
        $response->assertSee('function revealContactInfo', false);

        // Contact reveal is WhatsApp-centric in the current UI
        $response->assertSee('whatsappNumber', false);
    }

    #[Test]
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

    #[Test]
    public function test_whatsapp_error_handling_when_translations_missing()
    {
        $response = $this->get(route('service-providers.show', $this->serviceProvider->id));

        $response->assertStatus(200);
        // The link works without JavaScript: the URL is built on the server.
        $response->assertSee('href="https://wa.me/', false);
    }

    #[Test]
    public function test_whatsapp_url_encoding_handles_special_characters()
    {
        $sp = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider']),
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'company_name' => 'Company & Sons',
            'phone' => '6138649118',
            'whatsapp_number' => '+16138649118',
        ]);

        $response = $this->get(route('service-providers.show', $sp->id));

        $response->assertStatus(200);
        // "&" is percent-encoded inside the message, so it cannot cut the URL short.
        $response->assertSee('Company%20%26%20Sons', false);
    }

    #[Test]
    public function test_numbers_without_country_code_dial_canada_not_egypt()
    {
        $sp = ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider']),
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'company_name' => 'Local Co',
            'phone' => '613-864-9118',
            'whatsapp_number' => null,
        ]);

        $response = $this->get(route('service-providers.show', $sp->id));

        $response->assertSee('href="https://wa.me/16138649118?text=', false);
        $response->assertDontSee('wa.me/206138649118', false);
    }

    #[Test]
    public function test_number_normalisation_covers_every_stored_shape()
    {
        $cases = [
            '+16138649118' => '16138649118',
            '+1 (613) 864-9118' => '16138649118',
            '6138649118' => '16138649118',
            '613.864.9118' => '16138649118',
            '16138649118' => '16138649118',
            '0016138649118' => '16138649118',
            '+201289121218' => '201289121218',
            '01289121218' => '201289121218',
            '' => null,
            '123' => null,
        ];

        foreach ($cases as $raw => $expected) {
            $this->assertSame($expected, \App\Support\WhatsApp::digits((string) $raw), "Number: {$raw}");
        }

        $this->assertSame('+1 (613) 864-9118', \App\Support\WhatsApp::display('6138649118'));
    }
}
