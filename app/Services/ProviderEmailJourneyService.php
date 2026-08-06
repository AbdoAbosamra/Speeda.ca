<?php

namespace App\Services;

use App\Mail\Provider\AddExperienceEmail;
use App\Mail\Provider\AddProfilePhotoEmail;
use App\Mail\Provider\AddServiceAreasEmail;
use App\Mail\Provider\AddServicesEmail;
use App\Mail\Provider\BuildReputationEmail;
use App\Mail\Provider\ProfileCompleteEmail;
use App\Mail\Provider\ShowcaseWorkEmail;
use App\Mail\Provider\WelcomeEmail;
use App\Mail\Provider\WriteDescriptionEmail;
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
 * Email sequence (each step unlocks only after the previous is complete):
 *  welcome → photo → services → bio → experience → gallery → service_areas → complete → reviews
 */
class ProviderEmailJourneyService
{
    // ---------------------------------------------------------------------------
    //  Email type constants  (also stored verbatim in provider_email_logs.email_type)
    // ---------------------------------------------------------------------------

    public const EMAIL_WELCOME       = 'welcome';
    public const EMAIL_PHOTO         = 'photo';
    public const EMAIL_SERVICES      = 'services';
    public const EMAIL_BIO           = 'bio';
    public const EMAIL_EXPERIENCE    = 'experience';
    public const EMAIL_GALLERY       = 'gallery';
    public const EMAIL_SERVICE_AREAS = 'service_areas';
    public const EMAIL_COMPLETE      = 'complete';
    public const EMAIL_REVIEWS       = 'reviews';

    /**
     * Maximum resend attempts per email type (beyond the initial send).
     * Indexed by email type → max additional attempts (total = 1 initial + N resends).
     */
    private const MAX_ATTEMPTS = [
        self::EMAIL_WELCOME       => 1,   // Never resend – only sent once
        self::EMAIL_PHOTO         => 3,   // 1 initial + 2 resends
        self::EMAIL_SERVICES      => 3,
        self::EMAIL_BIO           => 3,
        self::EMAIL_EXPERIENCE    => 3,
        self::EMAIL_GALLERY       => 3,
        self::EMAIL_SERVICE_AREAS => 3,
        self::EMAIL_COMPLETE      => 1,   // Never resend – celebration email
        self::EMAIL_REVIEWS       => 4,   // 1 initial + 3 resends
    ];

    /**
     * Days until next resend attempt, indexed by attempt_number (1-based).
     * Attempt 1 = initial send; attempt 2 = first resend, etc.
     * The value is the number of DAYS to wait before the NEXT send.
     */
    private const RESEND_DAYS = [
        self::EMAIL_PHOTO         => [1 => 5, 2 => 7, 3 => 14],
        self::EMAIL_SERVICES      => [1 => 5, 2 => 7, 3 => 10],
        self::EMAIL_BIO           => [1 => 5, 2 => 7, 3 => 10],
        self::EMAIL_EXPERIENCE    => [1 => 5, 2 => 7, 3 => 10],
        self::EMAIL_GALLERY       => [1 => 5, 2 => 10, 3 => 14],
        self::EMAIL_SERVICE_AREAS => [1 => 5, 2 => 7, 3 => 10],
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
        if (!$user || !$user->is_active || empty($user->email)) {
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
        if (!$user || empty($user->email)) {
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
                ->queue(new WelcomeEmail($provider));

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

        // Step 1 – Profile photo
        if (!$this->hasProfilePhoto($provider)) {
            if (!$this->emailSent($logs, self::EMAIL_PHOTO)) {
                return self::EMAIL_PHOTO;
            }
            return null; // Waiting for resend or max attempts reached
        }

        // Step 2 – Services
        if (!$this->hasServices($provider)) {
            if (!$this->emailSent($logs, self::EMAIL_SERVICES)) {
                return self::EMAIL_SERVICES;
            }
            return null;
        }

        // Step 3 – Business description (bio)
        if (!$this->hasBio($provider)) {
            if (!$this->emailSent($logs, self::EMAIL_BIO)) {
                return self::EMAIL_BIO;
            }
            return null;
        }

        // Step 4 – Years of experience
        if (!$this->hasExperience($provider)) {
            if (!$this->emailSent($logs, self::EMAIL_EXPERIENCE)) {
                return self::EMAIL_EXPERIENCE;
            }
            return null;
        }

        // Step 5 – Gallery photos
        if (!$this->hasGallery($provider)) {
            if (!$this->emailSent($logs, self::EMAIL_GALLERY)) {
                return self::EMAIL_GALLERY;
            }
            return null;
        }

        // Step 6 – Service areas
        if (!$this->hasServiceAreas($provider)) {
            if (!$this->emailSent($logs, self::EMAIL_SERVICE_AREAS)) {
                return self::EMAIL_SERVICE_AREAS;
            }
            return null;
        }

        // Step 7 – Profile complete celebration
        if (!$this->emailSent($logs, self::EMAIL_COMPLETE)) {
            return self::EMAIL_COMPLETE;
        }

        // Step 8 – Reviews (send 3 days after the complete email)
        $completeSentAt = $logs[self::EMAIL_COMPLETE]?->sent_at;
        $reviewsReady   = $completeSentAt && $completeSentAt->diffInDays(now()) >= 3;
        $hasReviews     = $provider->activeReviews()->count() > 0;

        if (!$hasReviews && $reviewsReady && !$this->emailSent($logs, self::EMAIL_REVIEWS)) {
            return self::EMAIL_REVIEWS;
        }

        return null; // 🎉 All done
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

    public function hasServiceAreas(ServiceProvider $provider): bool
    {
        return $provider->serviceAreas()->where('is_active', true)->count() > 0;
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
            'has_service_areas' => $this->hasServiceAreas($provider),
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
            // Skip if the step is already complete
            if ($log->completed_at !== null) {
                continue;
            }

            // Skip if no resend is scheduled
            if ($log->next_send_at === null) {
                continue;
            }

            // Check if the resend time has arrived
            if ($log->next_send_at->isPast() || $log->next_send_at->isNow()) {
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
            self::EMAIL_PHOTO         => !$this->hasProfilePhoto($provider),
            self::EMAIL_SERVICES      => !$this->hasServices($provider),
            self::EMAIL_BIO           => !$this->hasBio($provider),
            self::EMAIL_EXPERIENCE    => !$this->hasExperience($provider),
            self::EMAIL_GALLERY       => !$this->hasGallery($provider),
            self::EMAIL_SERVICE_AREAS => !$this->hasServiceAreas($provider),
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
        if (!$user || empty($user->email)) {
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
            if (in_array($type, [self::EMAIL_WELCOME, self::EMAIL_COMPLETE])) {
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

        // Map each email type to the anchor id of the matching edit section.
        $anchors = [
            self::EMAIL_PHOTO         => '#edit-photo',
            self::EMAIL_SERVICES      => '#edit-services',
            self::EMAIL_BIO           => '#edit-basic-info',
            self::EMAIL_EXPERIENCE    => '#edit-basic-info',
            self::EMAIL_GALLERY       => '#edit-gallery',
            self::EMAIL_SERVICE_AREAS => '#edit-service-areas',
            self::EMAIL_REVIEWS       => '#reviews',
        ];

        $anchor = $anchors[$type] ?? '';
        $dashboardUrl = $baseUrl . $anchor;

        return match ($type) {
            self::EMAIL_WELCOME       => new WelcomeEmail($provider, $baseUrl),
            self::EMAIL_PHOTO         => new AddProfilePhotoEmail($provider, $dashboardUrl),
            self::EMAIL_SERVICES      => new AddServicesEmail($provider, $dashboardUrl),
            self::EMAIL_BIO           => new WriteDescriptionEmail($provider, $dashboardUrl),
            self::EMAIL_EXPERIENCE    => new AddExperienceEmail($provider, $dashboardUrl),
            self::EMAIL_GALLERY       => new ShowcaseWorkEmail($provider, $dashboardUrl),
            self::EMAIL_SERVICE_AREAS => new AddServiceAreasEmail($provider, $dashboardUrl),
            self::EMAIL_COMPLETE      => new ProfileCompleteEmail($provider, $baseUrl),
            self::EMAIL_REVIEWS       => new BuildReputationEmail($provider, $dashboardUrl),
            default                   => throw new \InvalidArgumentException("Unknown email type: {$type}"),
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

        $pendingResends = ProviderEmailLog::whereNotNull('next_send_at')
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
