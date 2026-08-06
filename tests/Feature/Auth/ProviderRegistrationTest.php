<?php

namespace Tests\Feature\Auth;

use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private Category $profession;

    protected function setUp(): void
    {
        parent::setUp();

        $section = Category::factory()->create(['is_section' => true, 'is_active' => true]);
        $this->profession = Category::factory()->create([
            'parent_id' => $section->id,
            'is_section' => false,
            'is_active' => true,
        ]);

        Location::firstOrCreate(['city' => 'Montreal'], ['is_active' => true, 'country' => 'Canada']);
    }

    public function test_provider_registration_creates_profile()
    {
        $email = 'pro@example.com';

        $response = $this->post('/register', [
            'name' => 'Provider User',
            'email' => $email,
            'mobile' => '514-555-1234',
            'role' => 'service_provider',
            'profession' => (string) $this->profession->id,
            'city' => 'Montreal',
            'terms' => true,
            'password' => 'SecureReset123!',
            'password_confirmation' => 'SecureReset123!',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals('service_provider', $user->role);

        // A ServiceProvider record (the provider profile) should exist.
        $serviceProvider = ServiceProvider::where('user_id', $user->id)->first();
        $this->assertNotNull($serviceProvider, 'ServiceProvider was not created');

        $response->assertRedirect(route('service-providers.show', $serviceProvider->id, false));
    }

    public function test_client_registration_does_not_create_profile()
    {
        $email = 'client@example.com';

        $response = $this->post('/register', [
            'name' => 'Client User',
            'email' => $email,
            'role' => 'client',
            'password' => 'SecureReset123!',
            'password_confirmation' => 'SecureReset123!',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals('client', $user->role);

        $this->assertNull(
            ServiceProvider::where('user_id', $user->id)->first(),
            'ServiceProvider should not be created for clients'
        );

        $response->assertRedirect(route('home', absolute: false));
    }
}
