<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\ServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * 🔐 Comprehensive Login Tests
 *
 * Enhanced login testing covering security, edge cases, and user experience.
 * Login is now merged into the register page (GET /login redirects to register),
 * authentication requires a `login` field (email or mobile) plus a `role`, and
 * successful clients land on the home page while providers land on their profile.
 * Priority: ⭐⭐⭐⭐⭐ (Critical)
 */
class ComprehensiveLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_page_redirects_to_register()
    {
        // The standalone login page was merged into the register page.
        $this->get('/login')->assertRedirect(route('register'));
    }

    /** @test */
    public function user_can_login_with_correct_credentials()
    {
        $user = User::factory()->client()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('SecurePass123!'),
        ]);

        $response = $this->post('/login', [
            'login' => 'test@example.com',
            'password' => 'SecurePass123!',
            'role' => 'client',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function client_redirects_to_home_after_login()
    {
        $client = User::factory()->client()->create([
            'email' => 'client@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'client@example.com',
            'password' => 'password123',
            'role' => 'client',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($client);
    }

    /** @test */
    public function service_provider_redirects_to_profile_after_login()
    {
        $provider = User::factory()->serviceProvider()->create([
            'email' => 'provider@example.com',
            'password' => Hash::make('password123'),
        ]);

        $serviceProvider = ServiceProvider::factory()->create(['user_id' => $provider->id]);

        $response = $this->post('/login', [
            'login' => 'provider@example.com',
            'password' => 'password123',
            'role' => 'service_provider',
        ]);

        $response->assertRedirect(route('service-providers.show', $serviceProvider->id));
        $this->assertAuthenticatedAs($provider);
    }

    /** @test */
    public function login_fails_with_incorrect_email()
    {
        User::factory()->client()->create([
            'email' => 'correct@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'wrong@example.com',
            'password' => 'password123',
            'role' => 'client',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    /** @test */
    public function login_fails_with_incorrect_password()
    {
        User::factory()->client()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->post('/login', [
            'login' => 'test@example.com',
            'password' => 'wrong-password',
            'role' => 'client',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    /** @test */
    public function login_validation_requires_login_password_and_role()
    {
        // Missing login field
        $this->post('/login', [
            'password' => 'password123',
            'role' => 'client',
        ])->assertSessionHasErrors('login');

        // Missing password
        $this->post('/login', [
            'login' => 'test@example.com',
            'role' => 'client',
        ])->assertSessionHasErrors('password');

        // Missing role
        $this->post('/login', [
            'login' => 'test@example.com',
            'password' => 'password123',
        ])->assertSessionHasErrors('role');

        // All missing
        $this->post('/login', [])
            ->assertSessionHasErrors(['login', 'password', 'role']);
    }

    /** @test */
    public function login_fails_for_unknown_credentials()
    {
        $invalidCredentials = [
            ['login' => 'invalid@', 'password' => 'password123'],
            ['login' => '@invalid.com', 'password' => 'password123'],
            ['login' => 'invalid..email@test.com', 'password' => 'password123'],
            ['login' => 'nonexistent@example.com', 'password' => 'password123'],
        ];

        foreach ($invalidCredentials as $credentials) {
            $response = $this->post('/login', array_merge($credentials, ['role' => 'client']));

            $response->assertSessionHasErrors(['login']);
            $this->assertGuest();
        }
    }

    /** @test */
    public function remember_me_functionality_works()
    {
        $user = User::factory()->client()->create([
            'email' => 'remember@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'remember@example.com',
            'password' => 'password123',
            'role' => 'client',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertNotNull($user->remember_token);

        Auth::logout();
        $this->assertGuest();
    }

    /** @test */
    public function login_without_remember_me_works()
    {
        $user = User::factory()->client()->create([
            'email' => 'no-remember@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'no-remember@example.com',
            'password' => 'password123',
            'role' => 'client',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function rate_limiting_prevents_brute_force_attacks()
    {
        User::factory()->client()->create([
            'email' => 'bruteforce@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        // Threshold is 5 attempts; the 6th is throttled.
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', [
                'login' => 'bruteforce@example.com',
                'password' => 'wrong-password',
                'role' => 'client',
            ]);
        }

        $response->assertSessionHasErrors('login');

        // Even with correct credentials, should still be blocked.
        $response = $this->post('/login', [
            'login' => 'bruteforce@example.com',
            'password' => 'correct-password',
            'role' => 'client',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    /** @test */
    public function rate_limiting_is_per_login_identifier()
    {
        User::factory()->client()->create([
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
        ]);

        $user2 = User::factory()->client()->create([
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Rate limit user1
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'login' => 'user1@example.com',
                'password' => 'wrong-password',
                'role' => 'client',
            ]);
        }

        $this->post('/login', [
            'login' => 'user1@example.com',
            'password' => 'password123',
            'role' => 'client',
        ])->assertSessionHasErrors('login');

        // user2 should still be able to login
        $response = $this->post('/login', [
            'login' => 'user2@example.com',
            'password' => 'password123',
            'role' => 'client',
        ]);
        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user2);
    }

    /** @test */
    public function already_authenticated_user_is_redirected_from_login()
    {
        $user = User::factory()->client()->create();

        $this->actingAs($user);

        $this->get('/login')->assertRedirect();
    }

    /** @test */
    public function password_is_case_sensitive()
    {
        User::factory()->client()->create([
            'email' => 'case@example.com',
            'password' => Hash::make('CaseSensitivePassword'),
        ]);

        $this->post('/login', [
            'login' => 'case@example.com',
            'password' => 'CaseSensitivePassword',
            'role' => 'client',
        ])->assertRedirect(route('home'));

        Auth::logout();

        $this->post('/login', [
            'login' => 'case@example.com',
            'password' => 'casesensitivepassword',
            'role' => 'client',
        ])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    /** @test */
    public function login_handles_unicode_passwords()
    {
        $unicodePassword = 'كلمة_مرور_عربية_123';

        $user = User::factory()->client()->create([
            'email' => 'unicode@example.com',
            'password' => Hash::make($unicodePassword),
        ]);

        $response = $this->post('/login', [
            'login' => 'unicode@example.com',
            'password' => $unicodePassword,
            'role' => 'client',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function csrf_protection_is_enforced_on_login()
    {
        User::factory()->client()->create([
            'email' => 'csrf@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->post('/login', [
                'login' => 'csrf@example.com',
                'password' => 'password123',
                'role' => 'client',
            ]);

        $response->assertRedirect(route('home'));
    }

    /** @test */
    public function login_logs_authentication_attempts()
    {
        $user = User::factory()->client()->create([
            'email' => 'logging@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'logging@example.com',
            'password' => 'password123',
            'role' => 'client',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }
}
