<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        // First, get the registration page to obtain CSRF token
        $this->get('/register');

        $response = $this->post('/register', [
            '_token' => csrf_token(),
            'name' => 'Test User',
            'email' => 'test@example.com',
            'mobile' => '514-555-0000',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'client',
        ]);

        $response->dumpSession();
        $this->assertAuthenticated();
        $response->assertRedirect(route('location', absolute: false));
    }
}
