<?php

namespace Tests\Feature;

use App\Mail\Client\WelcomeClientEmail;
use App\Mail\Provider\BuildReputationEmail;
use App\Models\ProviderEmailLog;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Services\ProviderEmailJourneyService;
use App\Services\UserEngagementEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

/**
 * Provider emails are now only the welcome email and the reviews email, and
 * no account receives an email twice — including after a new release is
 * deployed.
 */
class EmailSendOnceTest extends TestCase
{
    use RefreshDatabase;

    private function provider(): ServiceProvider
    {
        return ServiceProvider::factory()->create([
            'user_id' => User::factory()->create(['role' => 'service_provider', 'is_active' => true])->id,
            'is_active' => true,
        ])->load('user');
    }

    public function test_retired_onboarding_reminders_are_never_resent(): void
    {
        Mail::fake();
        $provider = $this->provider();

        // An old "add your photo" email whose next reminder is overdue.
        ProviderEmailLog::create([
            'service_provider_id' => $provider->id,
            'email_type' => 'photo',
            'attempt_number' => 1,
            'sent_at' => now()->subDays(20),
            'next_send_at' => now()->subDays(5),
        ]);

        $sent = app(ProviderEmailJourneyService::class)->processProvider($provider);

        $this->assertNull($sent);
        Mail::assertNothingQueued();
        Mail::assertNothingSent();
    }

    public function test_the_welcome_email_is_sent_once(): void
    {
        Mail::fake();
        $provider = $this->provider();
        $svc = app(ProviderEmailJourneyService::class);

        $svc->sendWelcomeEmail($provider);
        $svc->sendWelcomeEmail($provider);

        Mail::assertQueued(\App\Mail\Provider\WelcomeEmail::class, 1);
        $this->assertSame(1, ProviderEmailLog::where('email_type', 'welcome')->count());
    }

    public function test_the_reviews_email_needs_a_complete_profile_and_goes_out_once(): void
    {
        Mail::fake();
        $provider = $this->provider();
        ProviderEmailLog::create([
            'service_provider_id' => $provider->id,
            'email_type' => 'welcome',
            'attempt_number' => 1,
            'sent_at' => now()->subDays(10),
            'completed_at' => now()->subDays(10),
        ]);

        // Incomplete profile: nothing to ask for yet.
        $this->assertNull(app(ProviderEmailJourneyService::class)->processProvider($provider));

        $svc = Mockery::mock(ProviderEmailJourneyService::class)->makePartial();
        $svc->shouldReceive('isProfileComplete')->andReturn(true);

        $this->assertSame('reviews', $svc->processProvider($provider));
        // Running the scheduler again the same day sends nothing more.
        $this->assertNull($svc->processProvider($provider->fresh('user')));

        Mail::assertQueued(BuildReputationEmail::class, 1);
    }

    public function test_the_release_migration_marks_existing_accounts_as_already_emailed(): void
    {
        Mail::fake();

        $oldClient = User::factory()->create(['role' => 'client', 'is_active' => true, 'client_welcome_email_sent_at' => null]);
        $provider = $this->provider();
        \App\Models\Review::factory()->create([
            'client_id' => $oldClient->id,
            'service_provider_id' => $provider->id,
            'is_active' => true,
        ]);
        $oldClient->forceFill(['first_review_email_sent_at' => null])->saveQuietly();
        $provider->forceFill(['first_review_received_email_sent_at' => null])->saveQuietly();

        $migration = require database_path('migrations/2026_10_10_000002_retire_provider_onboarding_emails_and_baseline_send_flags.php');
        $migration->up();

        $this->assertNotNull($oldClient->fresh()->client_welcome_email_sent_at);
        $this->assertNotNull($oldClient->fresh()->first_review_email_sent_at);
        $this->assertNotNull($provider->fresh()->first_review_received_email_sent_at);

        // An existing client logging in after the release is not welcomed again.
        app(UserEngagementEmailService::class)->sendClientWelcomeEmail($oldClient->fresh());
        Mail::assertNotQueued(WelcomeClientEmail::class);

        // A brand-new client still gets the welcome, exactly once.
        $newClient = User::factory()->create(['role' => 'client', 'is_active' => true, 'client_welcome_email_sent_at' => null]);
        app(UserEngagementEmailService::class)->sendClientWelcomeEmail($newClient);
        app(UserEngagementEmailService::class)->sendClientWelcomeEmail($newClient->fresh());
        Mail::assertQueued(WelcomeClientEmail::class, 1);

        // Running the migration a second time changes nothing.
        $stamp = $oldClient->fresh()->client_welcome_email_sent_at;
        $migration->up();
        $this->assertEquals($stamp, $oldClient->fresh()->client_welcome_email_sent_at);
    }
}
