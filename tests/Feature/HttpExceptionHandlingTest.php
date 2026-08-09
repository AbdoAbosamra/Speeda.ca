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

        $this->get(URL::signedRoute('broadcast.unsubscribe', ['user' => $user->id]))
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

    public function test_a_genuine_unexpected_error_still_gets_the_friendly_response(): void
    {
        Route::get('/__boom', fn () => throw new \RuntimeException('kaboom'))
            ->middleware('web');

        // Not an HttpException, so the catch-all still owns it: a redirect for
        // browsers, and a 500 with a generic message for API clients.
        $this->get('/__boom')->assertStatus(302);

        $this->getJson('/__boom')
            ->assertStatus(500)
            ->assertJson(['error_code' => 'INTERNAL_ERROR'])
            ->assertJsonMissing(['message' => 'kaboom']);
    }
}
