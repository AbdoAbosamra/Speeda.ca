<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * config/trustedproxy.php: CloudPanel's internal nginx hop reaches PHP from
 * 127.0.0.1. Unless that hop is trusted, every visitor has the same IP, so
 * every guest shares one rate-limit bucket and analytics see one visitor.
 */
class TrustedProxyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/__whoami', fn () => response()->json([
            'ip' => request()->ip(),
            'secure' => request()->isSecure(),
        ]));
    }

    public function test_the_real_visitor_ip_is_read_through_the_local_proxy(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders([
                'X-Forwarded-For' => '203.0.113.5',
                'X-Forwarded-Proto' => 'https',
            ])
            ->get('/__whoami')
            ->assertJson(['ip' => '203.0.113.5', 'secure' => true]);
    }

    public function test_a_visitor_cannot_spoof_their_ip_with_a_header(): void
    {
        // Not from loopback, so X-Forwarded-For is ignored.
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.7'])
            ->withHeaders(['X-Forwarded-For' => '203.0.113.5'])
            ->get('/__whoami')
            ->assertJson(['ip' => '198.51.100.7', 'secure' => false]);
    }
}
