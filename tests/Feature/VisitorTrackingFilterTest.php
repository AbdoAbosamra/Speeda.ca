<?php

namespace Tests\Feature;

use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Only people opening pages are visitors: crawlers, background fetches and
 * error pages must not reach the visitors table.
 */
class VisitorTrackingFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_page_view_is_recorded(): void
    {
        $this->get('/', ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0) Chrome/130'])->assertOk();

        $this->assertSame(1, Visitor::count());
    }

    public function test_crawlers_are_not_visitors(): void
    {
        $this->get('/', ['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])->assertOk();

        $this->assertSame(0, Visitor::count());
    }

    public function test_background_json_requests_are_not_visitors(): void
    {
        $this->getJson('/current-locale', ['User-Agent' => 'Mozilla/5.0 Chrome/130']);

        $this->assertSame(0, Visitor::count());
    }

    public function test_profile_views_count_a_visitor_once_and_ignore_crawlers(): void
    {
        $provider = \App\Models\ServiceProvider::factory()->create([
            'user_id' => \App\Models\User::factory()->create(['role' => 'service_provider', 'is_active' => true])->id,
            'is_active' => true,
            'views' => 0,
        ]);
        $this->withCookie(config('session.cookie'), str_repeat('v', 40));

        $browser = ['User-Agent' => 'Mozilla/5.0 Chrome/130'];
        $this->get(route('service-providers.show', $provider), $browser)->assertOk();
        $this->get(route('service-providers.show', $provider), $browser)->assertOk();
        $this->get(route('service-providers.show', $provider), ['User-Agent' => 'Googlebot/2.1'])->assertOk();

        $this->assertSame(1, (int) $provider->fresh()->views);
    }

    public function test_missing_pages_are_not_visitors(): void
    {
        $this->get('/this-page-does-not-exist-' . uniqid(), ['User-Agent' => 'Mozilla/5.0 Chrome/130'])->assertNotFound();

        $this->assertSame(0, Visitor::count());
    }
}
