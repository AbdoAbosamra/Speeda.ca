<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * ops:preflight is the release checklist's window into production (tinker is
 * not installed there). It must report what is waiting to be emailed and must
 * never change anything.
 */
class OpsPreflightTest extends TestCase
{
    use RefreshDatabase;

    private function queueJob(string $displayName, int $ageMinutes): void
    {
        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode(['displayName' => $displayName]),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->subMinutes($ageMinutes)->timestamp,
        ]);
    }

    public function test_it_reports_waiting_emails_by_type_without_touching_them(): void
    {
        $this->queueJob('App\\Mail\\Client\\WelcomeClientEmail', 90);
        $this->queueJob('App\\Mail\\Client\\WelcomeClientEmail', 10);
        $this->queueJob('App\\Jobs\\SendProviderBroadcastEmail', 5);

        $this->artisan('ops:preflight')
            ->expectsOutputToContain('Queued jobs: 3')
            ->expectsOutputToContain('Failed jobs: 0')
            ->assertSuccessful();

        $this->assertSame(3, DB::table('jobs')->count());
    }

    public function test_it_runs_on_an_empty_queue(): void
    {
        $this->artisan('ops:preflight')
            ->expectsOutputToContain('Queued jobs: 0')
            ->expectsOutputToContain('Broadcast recipients still to send: 0 pending, 0 sending')
            ->assertSuccessful();
    }
}
