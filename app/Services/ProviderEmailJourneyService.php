<?php

namespace App\Services;

use App\Mail\Provider\BuildReputationEmail;
use App\Mail\Provider\WelcomeEmail;
use App\Models\ProviderEmailLog;
use App\Models\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * ProviderEmailJourneyService
 *
 * The brain of the automated onboarding email system.
 * It implements a state machine that determines which email each provider
 * should receive based on their current profile state, then sends it
 * and records the outcome in provider_email_logs.
 *
 * Provider emails now: the welcome email at registration, then one
 * "build your reputation" email (with up to 3 reminders) once the profile is
 * complete and still has no reviews. The profile-step reminders (photo,
 * services, description, experience, gallery, profile complete) were retired;
 * their old log rows stay for history and are never resent.
 */
class ProviderEmailJourneyService
{
    // ---------------------------------------------------------------------------
    //  Email type constants  (also stored verbatim in provider_email_logs.email_type)
    // ---------------------------------------------------------------------------

    public const EMAIL_WELCOME       = 'welcome';
    public const EMAIL_REVIEWS       = 'reviews';

    /** The emails this service can still send. */
    public const ACTIVE_TYPES = [self::EMAIL_WELCOME, self::EMAIL_REVIEWS];

    /** Retired onboarding steps: kept only to label old log rows. */
    public const RETIRED_TYPES = ['photo', 'services', 'bio', 'experience', 'gallery', 'complete'];

    /** Days after the welcome email before the reviews email may go out. */
    private const REVIEWS_AFTER_WELCOME_DAYS = 3;

    /**
     * Maximum resend attempts per email type (beyond the initial send).
     * Indexed by email type → max additional attempts (total = 1 initial + N resends).
     */
    private const MAX_ATTEMPTS = [
        self::EMAIL_WELCOME       => 1,   // Never resend – only sent once
        self::EMAIL_REVIEWS       => 4,   // 1 initial + 3 resends
    ];

    /**
     * Days until next resend attempt, indexed by attempt_number (1-based).
     * Attempt 1 = initial send; attempt 2 = first resend, etc.
     * The value is the number of DAYS to wait before the NEXT send.
     */
    private const RESEND_DAYS = [
        self::EMAIL_REVIEWS       => [1 => 3, 2 => 7, 3 => 14, 4 => 21],
    ];

    // ---------------------------------------------------------------------------
    //  Public API
    // ---------------------------------------------------------------------------

    /**
     * Main entry point called by the Artisan command for each active provider.
     *
     * @param ServiceProvider $provider  (with user relation loaded)
     * @param bool            $dryRun    If true, log what would happen but do not send.
     * @return string|null    The email type that was sent, or null if nothing was due.
     */
    public function processProvider(ServiceProvider $provider, bool $dryRun = false): ?string
    {
        // Ensure user exists and is active
        $user = $provider->user;
        if (!$user || !$user->is_active || empty($user->email) || $user->hasOptedOutOfEmail()) {
            return null;
        }

        // Load existing logs for this provider keyed by email_type
        $logs = ProviderEmailLog::where('service_provider_id', $provider->id)
            ->get()
            ->keyBy('email_type');

        // 1. Check if any existing email is due for a resend
        $resendType = $this->findDueResend($provider, $logs);
        if ($resendType) {
            return $this->sendEmail($provider, $resendType, $logs[$resendType] ?? null, $dryRun, true);
        }

        // 2. Otherwise, determine the next email in the journey
        $nextType = $this->determineNextEmail($provider, $logs);
        if (!$nextType) {
            return null; // Provider is fully done – nothing to send
        }

        return $this->sendEmail($provider, $nextType, null, $dryRun, false);
    }

    /**
     * Send the welcome email immediately when a new provider registers.
     * Called directly from AuthService; NOT by the scheduler.
     */
    public function sendWelcomeEmail(ServiceProvider $provider): void
    {
        $user = $provider->user;
        if (!$user || empty($user->email) || $user->hasOptedOutOfEmail()) {
            return;
        }

        // Guard: never send welcome more than once
        $alreadySent = ProviderEmailLog::where('service_provider_id', $provider->id)
            ->where('email_type', self::EMAIL_WELCOME)
            ->exists();

        if ($alreadySent) {
            return;
        }

        try {
            Mail::to($user->email, $provider->company_name ?? $user->name)
                ->queue(new WelcomeEmail($provider, route('service-providers.show', $provider->id)));

            ProviderEmailLog::create([
                'service_provider_id' => $provider->id,
                'email_type'          => self::EMAIL_WELCOME,
                'attempt_number'      => 1,
                'sent_at'             => now(),
                'next_send_at'        => null,  // No resend for welcome
                'completed_at'        => now(), // Welcome is always "complete"
            ]);

            Log::info('[EmailJourney] Welcome email queued', ['provider_id' => $provider->id]);
        } catch (\Throwable $e) {
            Log::error('[EmailJourney] Failed to queue welcome email', [
                'provider_id' => $provider->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    // ---------------------------------------------------------------------------
    //  State machine
    // ---------------------------------------------------------------------------

    /**
     * Determines which email type should be sent next for this provider.
     * Returns null if the provider is fully complete with no pending actions.
     */
    public function determineNextEmail(ServiceProvider $provider, $logs = null): ?string
    {
        if ($logs === null) {
            $logs = ProviderEmailLog::where('service_provider_id', $provider->id)
                ->get()
                ->keyBy('email_type');
        }

        // Each provider gets the reviews email at most once (plus its
        // scheduled reminders, handled by findDueResend).
        if ($this->emailSent($logs, self::EMAIL_REVIEWS)) {
            return null;
        }

        // Only providers who joined through the welcome email, and not on
        // their first days: there must be something to review first.
        $welcomeSentAt = ($logs[self::EMAIL_WELCOME] ?? null)?->sent_at;
        if (! $welcomeSentAt || $welcomeSentAt->diffInDays(now()) < self::REVIEWS_AFTER_WELCOME_DAYS) {
            return null;
        }

        // Asking for reviews only makes sense once the profile is complete.
        if (! $this->isProfileComplete($provider)) {
            return null;
        }

        if ($provider->activeReviews()->count() > 0) {
            return null;
        }

        return self::EMAIL_REVIEWS;
    }

    public function isProfileComplete(ServiceProvider $provider): bool
    {
        return $this->hasProfilePhoto($provider)
            && $this->hasServices($provider)
            && $this->hasBio($provider)
            && $this->hasExperience($provider)
            && $this->hasGallery($provider);
    }

    // ---------------------------------------------------------------------------
    //  Profile condition helpers
    // ---------------------------------------------------------------------------

    public function hasProfilePhoto(ServiceProvider $provider): bool
    {
        return filled($provider->profile_image);
    }

    public function hasServices(ServiceProvider $provider): bool
    {
        if (is_array($provider->services_offered)) {
            return count(array_filter($provider->services_offered)) > 0;
        }
        return filled($provider->services_offered) && $provider->services_offered !== '[]';
    }

    public function hasBio(ServiceProvider $provider): bool
    {
        $bio = trim((string) $provider->bio);
        if (empty($bio)) return false;
        // Treat the default placeholder bio as missing
        if ($bio === 'Service provider profile. Please complete your details.') return false;
        return strlen($bio) >= 30;
    }

    public function hasExperience(ServiceProvider $provider): bool
    {
        return filled($provider->experience_years) && (int) $provider->experience_years > 0;
    }

    public function hasGallery(ServiceProvider $provider): bool
    {
        return $provider->getMedia('gallery')->count() > 0;
    }

    /**
     * Build a full completion snapshot for a provider (used in admin panel).
     */
    public function getCompletionSnapshot(ServiceProvider $provider): array
    {
        return [
            'has_photo'         => $this->hasProfilePhoto($provider),
            'has_services'      => $this->hasServices($provider),
            'has_bio'           => $this->hasBio($provider),
            'has_experience'    => $this->hasExperience($provider),
            'has_gallery'       => $this->hasGallery($provider),
            'has_reviews'       => $provider->activeReviews()->count() > 0,
        ];
    }

    // ---------------------------------------------------------------------------
    //  Internal helpers
    // ---------------------------------------------------------------------------

    /**
     * Check if any existing log entry is currently due for a resend.
     */
    private function findDueResend(ServiceProvider $provider, $logs): ?string
    {
        foreach ($logs as $type => $log) {
            // Retired emails are never sent again, whatever their old schedule says.
            if (! in_array($type, self::ACTIVE_TYPES, true)) {
                continue;
            }

            // Skip if the step is already complete
            if ($log->completed_at !== null) {
                continue;
            }

            // Skip if no resend is scheduled
            if ($log->next_send_at === null) {
                continue;
            }

            // Check if the resend time has arrived
            if ($log->next_send_at->lessThanOrEqualTo(now())) {
                // Verify the step is STILL incomplete
                if ($this->isStepStillIncomplete($provider, $type)) {
                    return $type;
                }
            }
        }

        return null;
    }

    /**
     * Verify that a particular email step is still not completed by the provider.
     */
    private function isStepStillIncomplete(ServiceProvider $provider, string $emailType): bool
    {
        return match ($emailType) {
            self::EMAIL_REVIEWS       => $provider->activeReviews()->count() === 0,
            default                   => false,
        };
    }

    /**
     * Checks whether the first-ever send for a given email type has been made.
     */
    private function emailSent($logs, string $type): bool
    {
        return isset($logs[$type]);
    }

    /**
     * Core dispatch method: sends the email via queue, then records the log.
     */
    private function sendEmail(
        ServiceProvider $provider,
        string $type,
        ?ProviderEmailLog $existingLog,
        bool $dryRun,
        bool $isResend
    ): ?string {
        $user = $provider->user;
        if (!$user || empty($user->email) || $user->hasOptedOutOfEmail()) {
            return null;
        }

        $attemptNumber = $isResend ? (($existingLog?->attempt_number ?? 0) + 1) : 1;
        $maxAttempts   = self::MAX_ATTEMPTS[$type] ?? 1;

        if ($attemptNumber > $maxAttempts) {
            Log::debug('[EmailJourney] Max attempts reached, skipping', [
                'provider_id'    => $provider->id,
                'email_type'     => $type,
                'attempt_number' => $attemptNumber,
            ]);
            return null;
        }

        $nextSendAt = $this->calculateNextSendAt($type, $attemptNumber, $maxAttempts);

        if ($dryRun) {
            Log::info('[EmailJourney][DRY-RUN] Would send email', [
                'provider_id'    => $provider->id,
                'email'          => $user->email,
                'email_type'     => $type,
                'attempt_number' => $attemptNumber,
                'next_send_at'   => $nextSendAt?->toIso8601String(),
            ]);
            return $type;
        }

        try {
            $mailable = $this->buildMailable($type, $provider);

            Mail::to($user->email, $provider->company_name ?? $user->name)
                ->queue($mailable);

            // Update or create the log entry
            if ($isResend && $existingLog) {
                $existingLog->update([
                    'attempt_number' => $attemptNumber,
                    'sent_at'        => now(),
                    'next_send_at'   => $nextSendAt,
                ]);
            } else {
                ProviderEmailLog::create([
                    'service_provider_id' => $provider->id,
                    'email_type'          => $type,
                    'attempt_number'      => $attemptNumber,
                    'sent_at'             => now(),
                    'next_send_at'        => $nextSendAt,
                    'completed_at'        => null,
                ]);
            }

            // Mark completion emails as auto-completed
            if ($type === self::EMAIL_WELCOME) {
                ProviderEmailLog::where('service_provider_id', $provider->id)
                    ->where('email_type', $type)
                    ->update(['completed_at' => now()]);
            }

            Log::info('[EmailJourney] Email queued', [
                'provider_id'    => $provider->id,
                'email_type'     => $type,
                'attempt_number' => $attemptNumber,
                'next_send_at'   => $nextSendAt?->toIso8601String(),
            ]);

            return $type;
        } catch (\Throwable $e) {
            Log::error('[EmailJourney] Failed to queue email', [
                'provider_id' => $provider->id,
                'email_type'  => $type,
                'error'       => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Calculates when the next resend should fire.
     * Returns null if this is the last allowed attempt.
     */
    private function calculateNextSendAt(string $type, int $currentAttempt, int $maxAttempts): ?\Carbon\Carbon
    {
        if ($currentAttempt >= $maxAttempts) {
            return null; // No more resends after this
        }

        $days = self::RESEND_DAYS[$type][$currentAttempt] ?? null;

        return $days ? now()->addDays($days) : null;
    }

    /**
     * Instantiates the correct Mailable class for a given email type.
     * Each email receives a deep-link URL that scrolls the provider directly
     * to the relevant section on their profile page.
     */
    private function buildMailable(string $type, ServiceProvider $provider): \Illuminate\Mail\Mailable
    {
        $baseUrl = route('service-providers.show', $provider->id);

        return match ($type) {
            self::EMAIL_WELCOME       => new WelcomeEmail($provider, $baseUrl),
            self::EMAIL_REVIEWS       => new BuildReputationEmail($provider, $baseUrl . '#reviews'),
            default                   => throw new \InvalidArgumentException("Email type no longer sent: {$type}"),
        };
    }

    // ---------------------------------------------------------------------------
    //  Admin statistics helpers
    // ---------------------------------------------------------------------------

    /**
     * Returns aggregated email stats for the admin dashboard panel.
     */
    public function getAdminStats(): array
    {
        $totalSent = ProviderEmailLog::count();
        $totalProviders = ProviderEmailLog::distinct('service_provider_id')->count();

        $byType = ProviderEmailLog::select('email_type', DB::raw('COUNT(*) as total'))
            ->groupBy('email_type')
            ->pluck('total', 'email_type')
            ->toArray();

        $recentSends = ProviderEmailLog::with('serviceProvider.user')
            ->orderByDesc('sent_at')
            ->limit(20)
            ->get();

        $pendingResends = ProviderEmailLog::whereIn('email_type', self::ACTIVE_TYPES)
            ->whereNotNull('next_send_at')
            ->where('next_send_at', '<=', now()->addDays(1))
            ->whereNull('completed_at')
            ->count();

        return [
            'total_sent'       => $totalSent,
            'total_providers'  => $totalProviders,
            'by_type'          => $byType,
            'recent_sends'     => $recentSends,
            'pending_resends'  => $pendingResends,
        ];
    }

    /**
     * Returns the full email timeline for a specific provider.
     */
    public function getProviderEmailTimeline(ServiceProvider $provider): array
    {
        $logs = ProviderEmailLog::where('service_provider_id', $provider->id)
            ->orderBy('sent_at')
            ->get();

        $snapshot = $this->getCompletionSnapshot($provider);
        $nextEmail = $this->determineNextEmail($provider);

        return [
            'logs'       => $logs,
            'snapshot'   => $snapshot,
            'next_email' => $nextEmail,
        ];
    }
}
