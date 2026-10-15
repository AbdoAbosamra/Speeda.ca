<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Regression cover for the production exception handler.
 *
 * bootstrap/app.php registers a catch-all `\Exception` renderer that returns a
 * friendly redirect when APP_DEBUG is off. It used to catch HTTP exceptions
 * too, so with APP_DEBUG=false every 404 answered 302 to the homepage — a
 * soft-404 that search engines index as a redirect — and 403s were downgraded
 * the same way.
 *
 * Every test here forces app.debug to FALSE, because with debug on the handler
 * bails out early and the bug is invisible. That is exactly why it survived:
 * the suite runs with APP_DEBUG=true, production runs with it off.
 */
class HttpExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reproduce production conditions; without this the handler returns
        // early and none of these assertions can fail.
        config(['app.debug' => false]);
    }

    /* ================================================================== */
    /*  HTTP exceptions must keep their real status                        */
    /* ================================================================== */

    public function test_an_unknown_route_returns_404_not_a_redirect(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $response->assertStatus(404);
        $this->assertNotSame(302, $response->getStatusCode());
    }

    public function test_an_unknown_blog_slug_returns_404(): void
    {
        $this->get('/blogs/no-such-slug-'.uniqid())->assertStatus(404);
    }

    public function test_an_unknown_service_provider_returns_404(): void
    {
        $this->get('/service-providers/999999')->assertStatus(404);
    }

    public function test_an_unsigned_signed_url_returns_403_not_a_redirect(): void
    {
        $user = User::factory()->create();

        $response = $this->get('/email/unsubscribe/'.$user->id);

        $response->assertStatus(403);
        $this->assertNotSame(302, $response->getStatusCode());
    }

    public function test_a_valid_signed_url_still_works_with_debug_off(): void
    {
        $user = User::factory()->create();

        $this->get(URL::signedRoute('email.unsubscribe', ['user' => $user->id]))
            ->assertStatus(200);
    }

    public function test_a_non_admin_hitting_an_admin_route_gets_403(): void
    {
        $client = User::factory()->create(['role' => 'client', 'is_active' => true]);

        $response = $this->actingAs($client)->get(route('admin.dashboard'));

        $response->assertStatus(403);
        $this->assertNotSame(302, $response->getStatusCode());
    }

    /* ================================================================== */
    /*  Behaviour that must NOT change                                     */
    /* ================================================================== */

    public function test_a_guest_is_still_redirected_to_login(): void
    {
        // AuthenticationException is not an HttpException, so it keeps its
        // dedicated redirect handler.
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_successful_routes_are_unaffected(): void
    {
        $this->get('/')->assertStatus(200);
        $this->get('/up')->assertStatus(200);
        $this->get('/register')->assertStatus(200);
        $this->get('/blogs')->assertStatus(200);

        // /login answers 302 by design, and that must stay a redirect rather
        // than becoming an error page.
        $this->get('/login')->assertStatus(302);
    }

    public function test_a_page_that_fails_to_load_answers_500_not_a_redirect(): void
    {
        Route::get('/__boom', fn () => throw new \RuntimeException('kaboom'))
            ->middleware('web');

        // Redirecting a failed GET "back" sent visitors from Google back to
        // Google (back() follows the Referer) and looped on the homepage.
        $this->withHeader('Referer', 'https://www.google.com/search?q=speeda')
            ->get('/__boom')
            ->assertStatus(500)
            ->assertSee(__('errors.page_500_title'))
            ->assertDontSee('kaboom');

        $this->getJson('/__boom')
            ->assertStatus(500)
            ->assertJson(['error_code' => 'INTERNAL_ERROR'])
            ->assertJsonMissing(['message' => 'kaboom']);
    }

    public function test_a_failed_form_submission_returns_to_the_form_without_secrets(): void
    {
        Route::post('/__boom-form', fn () => throw new \RuntimeException('kaboom'))
            ->middleware('web');

        $this->from('/register')
            ->post('/__boom-form', ['name' => 'Sara', 'password' => 'secret-pass-123'])
            ->assertRedirect('/register')
            ->assertSessionHas('error', __('errors.server_error'))
            ->assertSessionHasInput('name', 'Sara')
            ->assertSessionMissing('_old_input.password');
    }

    /* ================================================================== */
    /*  Expired session (419), throttling (429), forbidden (403)           */
    /* ================================================================== */

    public function test_an_expired_form_goes_back_with_its_input_and_a_message(): void
    {
        // The test kernel skips CSRF checks, so raise what VerifyCsrfToken
        // raises. Laravel turns it into a 419 before any render callback.
        Route::post('/__expired', fn () => throw new \Illuminate\Session\TokenMismatchException())
            ->middleware('web');

        $this->from('/register')
            ->post('/__expired', ['email' => 'sara@example.com', 'password' => 'secret-pass-123'])
            ->assertRedirect('/register')
            ->assertSessionHas('error', __('errors.csrf_token_expired'))
            ->assertSessionHasInput('email', 'sara@example.com')
            ->assertSessionMissing('_old_input.password');
    }

    public function test_an_expired_ajax_request_gets_419_json_with_a_fresh_token(): void
    {
        Route::post('/__expired', fn () => throw new \Illuminate\Session\TokenMismatchException())
            ->middleware('web');

        $this->postJson('/__expired')
            ->assertStatus(419)
            ->assertJsonStructure(['message', 'csrf_token']);
    }

    public function test_too_many_requests_shows_the_branded_429_page(): void
    {
        Route::get('/__limited', fn () => 'ok')->middleware(['web', 'throttle:1,1']);

        $this->get('/__limited')->assertOk();

        $this->get('/__limited')
            ->assertStatus(429)
            ->assertSee(__('errors.page_429_title'));
    }

    public function test_the_403_page_is_branded_and_translated(): void
    {
        $client = User::factory()->create(['role' => 'client', 'is_active' => true]);

        $this->actingAs($client)
            ->get(route('admin.dashboard').'?lang=ar')
            ->assertStatus(403)
            ->assertSee(trans('errors.page_403_title', [], 'ar'));
    }
}
