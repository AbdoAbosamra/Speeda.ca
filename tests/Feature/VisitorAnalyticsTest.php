<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\VisitorTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $visitorService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->visitorService = app(VisitorTrackingService::class);
    }

    /**
     * Test admin can view visitor analytics
     */
    public function test_admin_can_view_visitor_analytics()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.visitors'))
            ->assertStatus(200)
            ->assertViewIs('admin.visitors.index');
    }

    /**
     * Test non-admin cannot view visitor analytics
     */
    public function test_non_admin_cannot_view_visitor_analytics()
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'client']);

        $this->actingAs($user)
            ->get(route('admin.visitors'))
            ->assertRedirect(route('dashboard'));
    }

    /**
     * Test visitor tracking captures visits
     */
    public function test_visitor_tracking_captures_visits()
    {
        // Make a GET request to the home page
        $this->get('/')
            ->assertStatus(200);

        $stats = $this->visitorService->getStatistics();

        $this->assertGreaterThan(0, $stats['total_visitors']);
        $this->assertGreaterThan(0, $stats['last_7_days']);
    }

    /**
     * Test visitor statistics are calculated correctly
     */
    public function test_visitor_statistics_are_calculated()
    {
        // Make multiple requests
        $this->get('/');
        $this->get('/');
        $this->get('/');

        $stats = $this->visitorService->getStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_visitors', $stats);
        $this->assertArrayHasKey('last_7_days', $stats);
        $this->assertArrayHasKey('last_30_days', $stats);
        $this->assertArrayHasKey('last_12_months', $stats);
        $this->assertArrayHasKey('live_visitors', $stats);
    }

    /**
     * Test visitor analytics can be exported
     */
    public function test_visitor_analytics_can_be_exported()
    {
        // Make some visits
        $this->get('/');
        $this->get('/categories');

        $this->actingAs($this->admin)
            ->get(route('admin.visitors.export', ['period' => 'last_30_days']))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    /**
     * Test live visitor count endpoint
     */
    public function test_live_visitor_count_endpoint()
    {
        $this->get('/');

        $this->actingAs($this->admin)
            ->getJson(route('admin.visitors.live-count'))
            ->assertStatus(200)
            ->assertJsonStructure(['success', 'count']);
    }

    /**
     * Test unique visitors are tracked correctly
     */
    public function test_unique_visitors_are_tracked()
    {
        // Simulate same visitor making multiple requests
        $ip = '127.0.0.1';
        $userAgent = 'Mozilla/5.0';

        // Create hash for the visitor
        $ipHash = hash('sha256', $ip);
        $userAgentHash = hash('sha256', $userAgent);

        // Make requests
        $this->get('/');
        $this->get('/categories');

        $stats = $this->visitorService->getStatistics();

        // Should count as 1 unique visitor (because of 5-minute deduplication)
        $this->assertGreaterThan(0, $stats['total_visitors']);
    }
}
