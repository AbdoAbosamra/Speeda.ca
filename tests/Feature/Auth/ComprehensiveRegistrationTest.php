<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * 🧪 Comprehensive Registration Tests
 *
 * Clients register with email + password only and land on the home page.
 * Service providers also need a mobile, a terminal (leaf) profession category,
 * an approved signup city, and accepted terms; they land on their public profile.
 * Priority: ⭐⭐⭐⭐⭐ (Critical)
 */
class ComprehensiveRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** A valid terminal profession category. */
    private Category $profession;

    protected function setUp(): void
    {
        parent::setUp();

        // A terminal profession = active, not a section, with no children.
        $section = Category::factory()->create(['is_section' => true, 'is_active' => true]);
        $this->profession = Category::factory()->create([
            'parent_id' => $section->id,
            'is_section' => false,
            'is_active' => true,
        ]);

        // An approved signup city must exist and be active.
        Location::firstOrCreate(['city' => 'Ottawa'], ['is_active' => true, 'country' => 'Canada']);
    }

    /**
     * Base payload for a valid service-provider registration.
     */
    private function providerData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Mike Provider',
            'email' => 'mike@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'service_provider',
            'mobile' => '(514) 555-1234',
            'profession' => $this->profession->id,
            'city' => 'Ottawa',
            'terms' => true,
        ], $overrides);
    }

    /** @test */
    public function registration_page_is_displayed()
    {
        $this->get('/register')
            ->assertStatus(200)
            ->assertViewIs('auth.register');
    }

    /** @test */
    public function client_registers_successfully_with_minimal_data()
    {
        Event::fake();

        $userData = [
            'name' => 'John Client',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'client',
        ];

        $response = $this->post('/register', $userData);

        $response->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', [
            'name' => 'John Client',
            'email' => 'john@example.com',
            'role' => 'client',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue(Hash::check('SecurePass123!', $user->password));
        $this->assertAuthenticatedAs($user);
        Event::assertDispatched(\Illuminate\Auth\Events\Registered::class);
    }

    /** @test */
    public function service_provider_registers_successfully_with_complete_data()
    {
        $response = $this->post('/register', $this->providerData([
            'whatsapp_number' => '514-555-5678',
        ]));

        $user = User::where('email', 'mike@example.com')->first();
        $this->assertNotNull($user);
        $response->assertRedirect(route('service-providers.show', $user->serviceProvider->id));

        $this->assertDatabaseHas('users', [
            'email' => 'mike@example.com',
            'role' => 'service_provider',
        ]);

        $serviceProvider = $user->serviceProvider;
        $this->assertEquals($this->profession->id, $serviceProvider->category_id);
        $this->assertEquals('+15145551234', $serviceProvider->phone);
        $this->assertEquals('+15145555678', $serviceProvider->whatsapp_number);
    }

    /** @test */
    public function service_provider_registration_requires_all_fields()
    {
        $baseData = [
            'name' => 'Test Provider',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'service_provider',
        ];

        // Missing mobile
        $this->post('/register', $baseData)->assertSessionHasErrors(['mobile']);

        // Missing profession
        $this->post('/register', array_merge($baseData, [
            'mobile' => '5145551234',
            'city' => 'Ottawa',
            'terms' => true,
        ]))->assertSessionHasErrors(['profession']);

        // Missing city
        $this->post('/register', array_merge($baseData, [
            'mobile' => '5145551234',
            'profession' => $this->profession->id,
            'terms' => true,
        ]))->assertSessionHasErrors(['city']);

        // Missing terms
        $this->post('/register', array_merge($baseData, [
            'mobile' => '5145551234',
            'profession' => $this->profession->id,
            'city' => 'Ottawa',
        ]))->assertSessionHasErrors(['terms']);
    }

    /** @test */
    public function email_validation_works_correctly()
    {
        $baseData = [
            'name' => 'Test User',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'client',
        ];

        $invalidEmails = [
            'invalid-email',
            'invalid@',
            '@invalid.com',
            'invalid..email@test.com',
            '',
        ];

        foreach ($invalidEmails as $email) {
            $this->post('/register', array_merge($baseData, ['email' => $email]))
                ->assertSessionHasErrors(['email']);
            $this->assertGuest();
        }
    }

    /** @test */
    public function email_uniqueness_is_enforced()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->post('/register', [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'client',
        ])->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    /** @test */
    public function password_validation_enforces_security_rules()
    {
        $baseData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'client',
        ];

        // Too short
        $this->post('/register', array_merge($baseData, [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]))->assertSessionHasErrors(['password']);

        // Mismatch
        $this->post('/register', array_merge($baseData, [
            'password' => 'SecurePass123!',
            'password_confirmation' => 'DifferentPass123!',
        ]))->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function mobile_phone_validation_works()
    {
        $invalidPhones = ['123', '01234567890', 'not-a-phone-number'];

        foreach ($invalidPhones as $phone) {
            $this->post('/register', $this->providerData([
                'email' => 'test' . rand() . '@example.com',
                'mobile' => $phone,
            ]))->assertSessionHasErrors(['mobile']);
        }

        $validPhones = ['5145551234', '(514) 555-1234', '514-555-1234', '+15145551234'];

        foreach ($validPhones as $phone) {
            User::truncate();
            ServiceProvider::truncate();

            $this->post('/register', $this->providerData([
                'email' => 'test' . rand() . '@example.com',
                'mobile' => $phone,
            ]))->assertSessionDoesntHaveErrors(['mobile']);
        }
    }

    /** @test */
    public function whatsapp_number_is_optional_but_validated_if_provided()
    {
        // Without WhatsApp - should pass
        $this->post('/register', $this->providerData())
            ->assertSessionDoesntHaveErrors(['whatsapp_number']);

        $this->resetRegistrationState();

        // Invalid WhatsApp
        $this->post('/register', $this->providerData([
            'email' => 'test2@example.com',
            'whatsapp_number' => '123',
        ]))->assertSessionHasErrors(['whatsapp_number']);

        $this->resetRegistrationState();

        // Valid WhatsApp
        $this->post('/register', $this->providerData([
            'email' => 'test3@example.com',
            'whatsapp_number' => '5149876543',
        ]))->assertSessionDoesntHaveErrors(['whatsapp_number']);
    }

    /** Clear rows and auth/session so another registration can run in the same test. */
    private function resetRegistrationState(): void
    {
        User::truncate();
        ServiceProvider::truncate();
        $this->flushSession();
        $this->app['auth']->logout();
    }

    /** @test */
    public function unicode_names_are_supported()
    {
        $testNames = [
            'محمد أحمد الخالدي',
            'François Müller',
            'José María García',
            'Владимир Петров',
        ];

        foreach ($testNames as $index => $name) {
            $this->post('/register', [
                'name' => $name,
                'email' => "unicode{$index}@example.com",
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
                'role' => 'client',
            ])->assertRedirect(route('home'));

            $this->assertDatabaseHas('users', [
                'name' => $name,
                'email' => "unicode{$index}@example.com",
            ]);

            // Log out so the next registration is not blocked by guest middleware.
            $this->flushSession();
            $this->app['auth']->logout();
        }
    }

    /** @test */
    public function registration_handles_duplicate_email()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'concurrent@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'client',
        ];

        $this->post('/register', $userData)->assertRedirect(route('home'));

        $this->flushSession();
        $this->app['auth']->logout();

        $this->post('/register', $userData)->assertSessionHasErrors(['email']);

        $this->assertEquals(1, User::where('email', 'concurrent@example.com')->count());
    }

    /** @test */
    public function role_based_redirects_work_correctly()
    {
        $this->post('/register', [
            'name' => 'Client User',
            'email' => 'client@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'client',
        ])->assertRedirect(route('home'));

        $this->flushSession();
        $this->app['auth']->logout();

        $this->post('/register', $this->providerData([
            'email' => 'provider@example.com',
        ]));
        $provider = User::where('email', 'provider@example.com')->first();
        $this->assertEquals('service_provider', $provider->role);
        $this->assertNotNull($provider->serviceProvider);
    }

    /** @test */
    public function csrf_protection_can_be_bypassed_in_tests()
    {
        $response = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'csrf@example.com',
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
                'role' => 'client',
            ]);

        $response->assertRedirect(route('home'));
    }

    /** @test */
    public function malicious_names_are_rejected_by_validation()
    {
        $this->post('/register', [
            'name' => '<script>alert("xss")</script>John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'role' => 'client',
        ])->assertSessionHasErrors(['name']);

        $this->assertGuest();
    }
}
