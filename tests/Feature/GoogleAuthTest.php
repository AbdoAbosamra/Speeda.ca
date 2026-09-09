<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Services\GoogleAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

/**
 * "Continue with Google", driven end to end with Google itself faked.
 *
 * The cases worth pinning down are the ones that go wrong quietly: an
 * unverified Google address being trusted, a second account appearing for
 * someone who already had one, and a provider being created without the
 * trade/city/phone their profile cannot exist without.
 */
class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.google.client_id', 'test-client-id');
        config()->set('services.google.client_secret', 'test-client-secret');
        config()->set('services.google.redirect', 'http://localhost/auth/google/callback');
    }

    /**
     * Stand in for the round trip to Google.
     */
    private function fakeGoogleUser(
        string $id = 'google-sub-123',
        string $email = 'newcomer@example.com',
        string $name = 'Nadia Farid',
        bool $verified = true,
    ): void {
        $socialiteUser = (new SocialiteUser)->setRaw([
            'sub' => $id,
            'email' => $email,
            'email_verified' => $verified,
        ])->map([
            'id' => $id,
            'name' => $name,
            'email' => $email,
        ]);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    private function seedProviderPrerequisites(): array
    {
        $location = Location::factory()->create(['city' => 'Montreal', 'is_active' => true]);
        $category = Category::factory()->create(['is_active' => true]);

        return [$location, $category];
    }

    private function selectOptions(string $html, string $containerId): array
    {
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query(sprintf(
            '//*[@id="%s"]//*[contains(concat(" ", normalize-space(@class), " "), " select-option ")]',
            $containerId,
        ));

        $options = [];

        foreach ($nodes as $node) {
            $options[] = [
                'value' => $node->getAttribute('data-value'),
                'label' => trim(preg_replace('/\s+/', ' ', $node->textContent)),
            ];
        }

        return $options;
    }

    /* ================================================================== */
    /*  Feature switch                                                     */
    /* ================================================================== */

    public function test_the_routes_are_hidden_when_no_credentials_are_configured(): void
    {
        config()->set('services.google.client_id', null);
        config()->set('services.google.client_secret', null);

        $this->get('/auth/google/redirect')->assertNotFound();
        $this->get('/auth/google/callback')->assertNotFound();
    }

    public function test_the_button_is_hidden_when_no_credentials_are_configured(): void
    {
        config()->set('services.google.client_id', null);
        config()->set('services.google.client_secret', null);

        $this->get(route('register'))->assertOk()->assertDontSee('Continue with Google');
    }

    public function test_the_button_is_shown_when_credentials_are_configured(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('Continue with Google');
    }

    public function test_each_google_button_reads_the_role_from_its_own_form(): void
    {
        $response = $this->get(route('register'))->assertOk();

        $this->assertSame(2, substr_count($response->getContent(), 'data-google-base='));
        $response->assertSee("const form = link.closest('form');", false);
        $response->assertSee("form?.querySelector('input[name=\"role\"]:checked')", false);
    }

    public function test_google_redirect_uses_the_configured_callback_and_remembers_provider_intent(): void
    {
        $response = $this->get('/auth/google/redirect?as=service_provider')->assertRedirect();
        $location = $response->headers->get('Location');

        $this->assertSame('accounts.google.com', parse_url($location, PHP_URL_HOST));

        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

        $this->assertSame(config('services.google.redirect'), $query['redirect_uri'] ?? null);
        $this->assertSame('service_provider', session(GoogleAuthService::SESSION_INTENT));
    }

    /**
     * Google returns to exactly one registered host. Starting the flow from a
     * different name for the same server — 127.0.0.1 rather than localhost, or
     * the bare domain rather than www — writes the session cookie under one
     * host and reads it back under another, so Socialite's state is gone on
     * return and sign-in fails with nothing explaining why. The flow has to
     * move to the canonical host before it leaves for Google.
     */
    public function test_sign_in_started_on_another_host_moves_to_the_callback_host_first(): void
    {
        config(['services.google.redirect' => 'http://localhost:8000/auth/google/callback']);

        $response = $this->get('http://127.0.0.1:8000/auth/google/redirect?as=service_provider')
            ->assertRedirect();

        $location = $response->headers->get('Location');

        $this->assertSame('localhost', parse_url($location, PHP_URL_HOST));
        $this->assertSame('/auth/google/redirect', parse_url($location, PHP_URL_PATH));
        // The intent has to survive the hop, or the provider lands in the
        // client sign-up form after a redirect they never asked for.
        $this->assertSame('as=service_provider', parse_url($location, PHP_URL_QUERY));
    }

    public function test_a_matching_host_goes_straight_to_google_without_a_detour(): void
    {
        config(['services.google.redirect' => 'http://localhost/auth/google/callback']);

        $location = $this->get('http://localhost/auth/google/redirect')
            ->assertRedirect()
            ->headers->get('Location');

        $this->assertSame('accounts.google.com', parse_url($location, PHP_URL_HOST));
    }

    /**
     * A port is not part of a cookie's identity, so localhost:8000 and
     * localhost share a session and must not be bounced between each other —
     * that would be an infinite redirect on any setup behind a proxy.
     */
    public function test_a_differing_port_alone_is_not_treated_as_a_different_host(): void
    {
        config(['services.google.redirect' => 'http://localhost:8000/auth/google/callback']);

        $location = $this->get('http://localhost/auth/google/redirect')
            ->assertRedirect()
            ->headers->get('Location');

        $this->assertSame('accounts.google.com', parse_url($location, PHP_URL_HOST));
    }

    /* ================================================================== */
    /*  New accounts                                                       */
    /* ================================================================== */

    public function test_a_new_provider_is_sent_to_the_details_form_instead_of_being_created(): void
    {
        $this->seedProviderPrerequisites();
        $this->fakeGoogleUser(email: 'pro@example.com');

        $this->withSession([GoogleAuthService::SESSION_INTENT => 'service_provider'])
            ->get('/auth/google/callback')
            ->assertRedirect(route('google.complete'));

        // Nothing is written until the form comes back: a provider row without
        // a trade, city and phone would be a broken public profile.
        $this->assertDatabaseMissing('users', ['email' => 'pro@example.com']);
        $this->assertGuest();

        $this->assertSame('service_provider', session(GoogleAuthService::SESSION_PENDING)['role']);
    }

    public function test_a_new_client_is_also_sent_to_the_details_form(): void
    {
        $this->seedProviderPrerequisites();
        $this->fakeGoogleUser(email: 'client@example.com', name: 'Sam Client');

        $this->withSession([GoogleAuthService::SESSION_INTENT => 'client'])
            ->get('/auth/google/callback')
            ->assertRedirect(route('google.complete'));

        // Google settles the address and a display name and nothing else, so
        // even a client still has a form to fill in - and no account exists
        // until they send it back.
        $this->assertDatabaseMissing('users', ['email' => 'client@example.com']);
        $this->assertSame('client', session(GoogleAuthService::SESSION_PENDING)['role']);
        $this->assertGuest();
    }

    public function test_the_details_form_offers_both_roles_and_pre_selects_the_one_pressed(): void
    {
        $this->seedProviderPrerequisites();

        $response = $this->withSession([GoogleAuthService::SESSION_PENDING => [
            'google_id' => 'google-sub-777',
            'email' => 'client@example.com',
            'name' => 'Sam Client',
            'role' => 'client',
            'created_at' => now()->timestamp,
        ]])->get('/auth/google/complete')->assertOk();

        $response->assertSee('name="role" value="client"', false);
        $response->assertSee('name="role" value="service_provider"', false);
        $response->assertSee('client@example.com');
        $response->assertSee('value="Sam Client"', false);

        // A client sees the provider block hidden, and - just as important -
        // not demanded: a `required` field they cannot see would stop the
        // browser submitting with an error bubble pointing at nothing.
        $content = $response->getContent();
        $this->assertStringContainsString('id="provider-fields"', $content);
        $this->assertMatchesRegularExpression('/id="provider-fields"[^>]*\shidden/', $content);
        $this->assertMatchesRegularExpression('/id="profession-input"[^>]*\sdisabled/', $content);
        $this->assertMatchesRegularExpression('/id="mobile"[^>]*\sdisabled/', $content);
    }

    public function test_the_details_form_demands_the_provider_fields_when_that_role_is_pressed(): void
    {
        $this->seedProviderPrerequisites();

        $content = $this->withSession([GoogleAuthService::SESSION_PENDING => [
            'google_id' => 'google-sub-999',
            'email' => 'pro@example.com',
            'name' => 'Rami Pro',
            'role' => 'service_provider',
            'created_at' => now()->timestamp,
        ]])->get('/auth/google/complete')->assertOk()->getContent();

        $this->assertDoesNotMatchRegularExpression('/id="provider-fields"[^>]*\shidden/', $content);
        $this->assertDoesNotMatchRegularExpression('/id="profession-input"[^>]*\sdisabled/', $content);
        $this->assertMatchesRegularExpression('/id="mobile"[^>]*\srequired/', $content);
        $this->assertStringContainsString('name="mobile_country_code" value="+1"', $content);
        $this->assertStringContainsString('name="whatsapp_country_code" value="+1"', $content);
    }

    public function test_google_and_normal_registration_offer_identical_professions_and_cities(): void
    {
        $this->seedProviderPrerequisites();

        $register = $this->get(route('register'))->assertOk()->getContent();
        $google = $this->withSession([GoogleAuthService::SESSION_PENDING => [
            'google_id' => 'google-sub-options',
            'email' => 'options@example.com',
            'name' => 'Options Test',
            'role' => 'service_provider',
            'created_at' => now()->timestamp,
        ]])->get('/auth/google/complete')->assertOk()->getContent();

        $this->assertSame(
            $this->selectOptions($register, 'profession-options'),
            $this->selectOptions($google, 'profession-options'),
        );
        $this->assertSame(
            $this->selectOptions($register, 'city-options'),
            $this->selectOptions($google, 'city-options'),
        );
    }

    public function test_a_client_is_created_once_the_details_form_is_submitted(): void
    {
        Mail::fake();

        $pending = [
            'google_id' => 'google-sub-777',
            'email' => 'client@example.com',
            'name' => 'Sam Client',
            'role' => 'client',
            'created_at' => now()->timestamp,
        ];

        $this->withSession([GoogleAuthService::SESSION_PENDING => $pending])
            ->post('/auth/google/complete', [
                'role' => 'client',
                'name' => 'Sam Client',
                'terms' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('home'));

        $user = User::where('email', 'client@example.com')->firstOrFail();

        $this->assertSame('client', $user->role);
        $this->assertSame('Sam Client', $user->name);
        $this->assertSame('google-sub-777', $user->google_id);
        $this->assertNull($user->password, 'A Google account should have no password.');
        $this->assertNotNull($user->email_verified_at, 'Google already verified the address.');
        $this->assertAuthenticatedAs($user);

        // A client signup must not drag a provider row along with it.
        $this->assertDatabaseMissing('service_providers', ['user_id' => $user->id]);
    }

    public function test_a_client_is_never_asked_for_the_provider_only_fields(): void
    {
        Mail::fake();

        // No trade, no city, no phone - none of it applies to a client, and
        // demanding it would strand them on the form.
        $this->withSession([GoogleAuthService::SESSION_PENDING => [
            'google_id' => 'google-sub-777',
            'email' => 'client@example.com',
            'name' => 'Sam Client',
            'role' => 'client',
            'created_at' => now()->timestamp,
        ]])
            ->post('/auth/google/complete', [
                'role' => 'client',
                'name' => 'Sam Client',
                'terms' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'client@example.com', 'role' => 'client']);
    }

    public function test_the_terms_must_be_accepted_by_a_client_too(): void
    {
        // The client half of the registration form carries its own checkbox;
        // this screen is the only place a Google client signup sees the terms.
        $this->withSession([GoogleAuthService::SESSION_PENDING => [
            'google_id' => 'google-sub-777',
            'email' => 'client@example.com',
            'name' => 'Sam Client',
            'role' => 'client',
            'created_at' => now()->timestamp,
        ]])
            ->post('/auth/google/complete', [
                'role' => 'client',
                'name' => 'Sam Client',
            ])
            ->assertSessionHasErrors('terms');

        $this->assertDatabaseMissing('users', ['email' => 'client@example.com']);
    }

    public function test_a_provider_is_created_once_the_details_form_is_submitted(): void
    {
        Mail::fake();
        [$location, $category] = $this->seedProviderPrerequisites();

        $pending = [
            'google_id' => 'google-sub-999',
            'email' => 'pro@example.com',
            'name' => 'Rami Pro',
            'role' => 'service_provider',
            'created_at' => now()->timestamp,
        ];

        $this->withSession([GoogleAuthService::SESSION_PENDING => $pending])
            ->post('/auth/google/complete', [
                'role' => 'service_provider',
                'name' => 'Rami Pro',
                'profession' => $category->id,
                'city' => 'Montreal',
                'mobile' => '4165550123',
                'whatsapp_number' => '514-555-0199',
                'terms' => '1',
            ])
            ->assertSessionHasNoErrors();

        $user = User::where('email', 'pro@example.com')->firstOrFail();

        $this->assertSame('service_provider', $user->role);
        $this->assertSame('google-sub-999', $user->google_id);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);

        $provider = ServiceProvider::where('user_id', $user->id)->firstOrFail();
        $this->assertSame($category->id, $provider->category_id);
        $this->assertSame($location->id, $provider->location_id);
        $this->assertSame('+14165550123', $provider->phone);
        $this->assertSame('+15145550199', $provider->whatsapp_number);
    }

    public function test_the_details_form_enforces_the_same_rules_as_normal_registration(): void
    {
        $this->seedProviderPrerequisites();

        $this->withSession([GoogleAuthService::SESSION_PENDING => [
            'google_id' => 'google-sub-999',
            'email' => 'pro@example.com',
            'name' => 'Rami Pro',
            'role' => 'service_provider',
            'created_at' => now()->timestamp,
        ]])
            ->post('/auth/google/complete', [
                'role' => 'service_provider',
                'name' => 'Rami Pro',
                'city' => 'Nowhere City',
                'mobile' => 'not-a-number',
            ])
            ->assertSessionHasErrors(['profession', 'city', 'mobile', 'terms']);

        $this->assertDatabaseMissing('users', ['email' => 'pro@example.com']);
    }

    public function test_an_abandoned_signup_cannot_be_resumed_later(): void
    {
        $this->seedProviderPrerequisites();

        // Stale stash: someone walked away and a different person sat down.
        $this->withSession([GoogleAuthService::SESSION_PENDING => [
            'google_id' => 'google-sub-999',
            'email' => 'pro@example.com',
            'name' => 'Rami Pro',
            'created_at' => now()->subHours(2)->timestamp,
        ]])
            ->get('/auth/google/complete')
            ->assertRedirect(route('register'));

        $this->assertGuest();
    }

    /* ================================================================== */
    /*  Existing accounts                                                  */
    /* ================================================================== */

    public function test_an_existing_password_account_is_linked_rather_than_duplicated(): void
    {
        $existing = User::factory()->create([
            'email' => 'known@example.com',
            'role' => 'client',
            'is_active' => true,
        ]);

        $this->fakeGoogleUser(id: 'google-sub-abc', email: 'known@example.com');

        $this->get('/auth/google/callback')->assertRedirect(route('home'));

        $this->assertSame(1, User::where('email', 'known@example.com')->count());
        $this->assertSame('google-sub-abc', $existing->fresh()->google_id);
        $this->assertNotNull($existing->fresh()->password, 'Linking must not wipe their password.');
        $this->assertAuthenticatedAs($existing);
    }

    public function test_a_returning_google_user_is_matched_by_subject_id_not_email(): void
    {
        // Their Google address changed since last time. The subject id is what
        // identifies them; matching on email alone would create a duplicate.
        $existing = User::factory()->create([
            'email' => 'old-address@example.com',
            'google_id' => 'google-sub-stable',
            'role' => 'client',
            'is_active' => true,
        ]);

        $this->fakeGoogleUser(id: 'google-sub-stable', email: 'new-address@example.com');

        $this->get('/auth/google/callback');

        $this->assertSame(1, User::count());
        $this->assertAuthenticatedAs($existing);
    }

    public function test_a_disabled_account_cannot_get_in_through_google(): void
    {
        User::factory()->create([
            'email' => 'banned@example.com',
            'is_active' => false,
        ]);

        $this->fakeGoogleUser(email: 'banned@example.com');

        $this->get('/auth/google/callback')->assertRedirect(route('register'));

        $this->assertGuest();
    }

    /* ================================================================== */
    /*  Refusals                                                           */
    /* ================================================================== */

    public function test_an_unverified_google_address_is_refused(): void
    {
        // Some Workspace setups hand back an address the user has not proven.
        // Trusting it would let someone claim an email they do not own, and
        // through the linking rule above, somebody else's Speeda account.
        $this->fakeGoogleUser(email: 'spoofed@example.com', verified: false);

        $this->get('/auth/google/callback')->assertRedirect(route('register'));

        $this->assertDatabaseMissing('users', ['email' => 'spoofed@example.com']);
        $this->assertGuest();
    }

    public function test_cancelling_at_google_returns_quietly(): void
    {
        $this->get('/auth/google/callback?error=access_denied')
            ->assertRedirect(route('register'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_a_google_only_account_is_told_why_its_password_does_not_work(): void
    {
        User::factory()->create([
            'email' => 'googler@example.com',
            'password' => null,
            'google_id' => 'google-sub-xyz',
            'role' => 'client',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'login' => 'googler@example.com',
            'password' => 'anything-at-all',
            'role' => 'client',
        ])->assertSessionHasErrors('login');

        $this->assertSame(
            __('auth.google_only_account'),
            session('errors')->first('login'),
        );
        $this->assertGuest();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
