<?php

namespace Tests\Feature\Admin;

use App\Actions\TrackProviderClickAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WhatsappAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private function skipUnlessMysql(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped('WhatsApp analytics aggregation relies on MySQL functions (HOUR/DAYOFWEEK/COUNT DISTINCT).');
        }
    }

    /**
     * Create a provider with a guaranteed-unique location/category to avoid
     * faker city collisions against the unique constraint on locations.city.
     */
    private function makeProvider(): \App\Models\ServiceProvider
    {
        $location = \App\Models\Location::factory()->create(['city' => 'TestCity_' . uniqid()]);
        $category = \App\Models\Category::factory()->create();

        return \App\Models\ServiceProvider::factory()
            ->forLocation($location->id)
            ->forCategory($category->id)
            ->create();
    }

    /** Guests are redirected to login. */
    public function test_guest_cannot_access_whatsapp_analytics(): void
    {
        $this->get(route('admin.whatsapp_analytics.index'))
            ->assertRedirect(route('login'));
    }

    /** Non-admins are forbidden (matches the platform AdminMiddleware). */
    public function test_non_admin_cannot_access_whatsapp_analytics(): void
    {
        $user = User::factory()->create(['role' => 'client']);

        $this->actingAs($user)
            ->get(route('admin.whatsapp_analytics.index'))
            ->assertForbidden();
    }

    /** Admin sees the analytics page. */
    public function test_admin_can_view_whatsapp_analytics(): void
    {
        $this->skipUnlessMysql();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.whatsapp_analytics.index'))
            ->assertStatus(200)
            ->assertViewIs('admin.analytics.whatsapp')
            ->assertSee('WhatsApp Analytics');
    }

    /** Click tracking records privacy-safe context (no IP, no raw UA). */
    public function test_click_tracking_enriches_context(): void
    {
        $this->skipUnlessMysql();

        $provider = $this->makeProvider();

        app(TrackProviderClickAction::class)->execute(
            $provider->id,
            'click_whatsapp',
            ['source_page' => 'listing']
        );

        $row = DB::table('analytics')
            ->where('provider_id', $provider->id)
            ->where('action_type', 'click_whatsapp')
            ->latest('id')
            ->first();

        $this->assertNotNull($row);
        $this->assertSame('listing', $row->source_page);
        $this->assertNotNull($row->device_type);
        $this->assertNotNull($row->session_hash);
        // PRIVACY: no real IP stored.
        $this->assertTrue(empty($row->ip_address) || str_starts_with($row->ip_address, 'hash:'));
    }

    /**
     * REGRESSION: guest (user_id IS NULL) clicks must be counted.
     * A naive whereNotIn('user_id', $adminIds) drops NULL rows.
     */
    public function test_guest_clicks_are_counted_null_safe(): void
    {
        $this->skipUnlessMysql();

        // Ensure at least one admin exists so the exclusion filter is active.
        User::factory()->create(['role' => 'admin']);
        $provider = $this->makeProvider();

        DB::table('analytics')->insert([
            ['provider_id' => $provider->id, 'user_id' => null, 'action_type' => 'view', 'session_hash' => 'g1', 'created_at' => now(), 'updated_at' => now()],
            ['provider_id' => $provider->id, 'user_id' => null, 'action_type' => 'click_whatsapp', 'session_hash' => 'g1', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Cache::flush();

        $summary = app(\App\Services\Admin\WhatsappAnalyticsService::class)->summary([
            'date_from' => null, 'date_to' => null, 'provider_id' => null,
            'category_id' => null, 'location_id' => null, 'source_page' => null,
            'locale' => null, 'device_type' => null,
        ]);

        $this->assertGreaterThanOrEqual(1, $summary['today']);
        $this->assertGreaterThanOrEqual(1, $summary['views_30']);
    }
}
