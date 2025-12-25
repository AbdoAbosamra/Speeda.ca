<?php

namespace Tests\Feature\Auth;

use App\Models\ServiceProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_registration_creates_profile()
    {
        // First, get the registration page to obtain CSRF token
        $this->get('/register');

        // Prepare request data
        $email = 'pro@example.com';

        $response = $this->post('/register', [
            '_token' => csrf_token(),
            'name' => 'Provider User',
            'email' => $email,
            'mobile' => '514-555-1234',
            'role' => 'service_provider',
            'profession' => '7', // Use category ID instead of name
            'city' => 'Montreal',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals('service_provider', $user->role);

        // Profile should exist
        $profile = ServiceProviderProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile, 'ServiceProviderProfile was not created');

        // Redirect to service provider show page (as controller does)
        $serviceProvider = \App\Models\ServiceProvider::where('user_id', $user->id)->first();
        $response->assertRedirect(route('service-providers.show', $serviceProvider->id, false));
    }

    public function test_client_registration_does_not_create_profile()
    {
        // First, get the registration page to obtain CSRF token
        $this->get('/register');

        $email = 'client@example.com';

        $response = $this->post('/register', [
            '_token' => csrf_token(),
            'name' => 'Client User',
            'email' => $email,
            'mobile' => '514-555-1234',
            'role' => 'client',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals('client', $user->role);

        $profile = ServiceProviderProfile::where('user_id', $user->id)->first();
        $this->assertNull($profile, 'ServiceProviderProfile should not be created for clients');

        $response->assertRedirect(route('location', [], false));
    }
}
