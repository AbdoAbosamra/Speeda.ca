<?php

namespace App\Services;

use App\Mail\Client\FifthReviewEmail;
use App\Mail\Client\FirstReviewEmail;
use App\Mail\Client\WelcomeClientEmail;
use App\Mail\Provider\FifthReviewReceivedEmail;
use App\Mail\Provider\FirstReviewReceivedEmail;
use App\Models\Review;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends milestone & motivational emails to client users and service providers:
 *  - Client Welcome Email upon registration
 *  - Client Review Milestones (1st approved review, 5th approved review)
 *  - Provider Review Milestones (1st received review, 5th received review)
 *
 * Each email is sent at most once, guarded by timestamp flags on the database tables.
 */
class UserEngagementEmailService
{
    /** Client milestones: approved-review count => flag column on users table. */
    private const CLIENT_MILESTONES = [
        1 => 'first_review_email_sent_at',
        5 => 'fifth_review_email_sent_at',
    ];

    /** Provider milestones: approved-review count => flag column on service_providers table. */
    private const PROVIDER_MILESTONES = [
        1 => 'first_review_received_email_sent_at',
        5 => 'fifth_review_received_email_sent_at',
    ];

    /**
     * Send welcome email to a regular client once, on their first login/registration.
     */
    public function sendClientWelcomeEmail(User $user): void
    {
        if (!$user->isClient() || !$user->email || $user->client_welcome_email_sent_at !== null) {
            return;
        }

        $updated = User::whereKey($user->id)
            ->whereNull('client_welcome_email_sent_at')
            ->update(['client_welcome_email_sent_at' => now()]);

        if ($updated === 0) {
            return;
        }

        $email = $user->email;

        DB::afterCommit(function () use ($user, $email) {
            try {
                $browseUrl = route('service-providers.index');
                Mail::to($email)->queue(new WelcomeClientEmail($user, $browseUrl));

                Log::info('Client welcome email queued', [
                    'user_id' => $user->id,
                    'email' => $email,
                ]);
            } catch (\Throwable $e) {
                User::whereKey($user->id)->update(['client_welcome_email_sent_at' => null]);

                Log::error('Failed to queue client welcome email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    /**
     * Triggered when a review is approved by an admin.
     * Evaluates milestone criteria for both the authoring client and receiving provider.
     */
    public function handleReviewApproved(Review $review): void
    {
        $review->loadMissing(['client', 'serviceProvider.user']);

        if ($review->client) {
            $this->handleClientReviewMilestones($review->client);
        }

        if ($review->serviceProvider) {
            $this->handleProviderReviewMilestones($review->serviceProvider);
        }
    }

    /**
     * Evaluate a client's approved-review count (1st / 5th milestone) and queue the matching email once.
     */
    private function handleClientReviewMilestones(User $user): void
    {
        if (!$user->isClient() || !$user->email) {
            return;
        }

        $approvedReviews = $user->reviews()->where('is_active', true)->count();

        $flagColumn = self::CLIENT_MILESTONES[$approvedReviews] ?? null;
        if ($flagColumn === null) {
            return; // Not a milestone count.
        }

        // Stamp the flag immediately so concurrent approvals cannot double-send.
        $updated = User::whereKey($user->id)
            ->whereNull($flagColumn)
            ->update([$flagColumn => now()]);

        if ($updated === 0) {
            return; // Already sent for this milestone.
        }

        $email = $user->email;

        DB::afterCommit(function () use ($user, $email, $approvedReviews) {
            try {
                $browseUrl = route('service-providers.index');

                $mailable = $approvedReviews >= 5
                    ? new FifthReviewEmail($user, $browseUrl)
                    : new FirstReviewEmail($user, $browseUrl);

                Mail::to($email)->queue($mailable);

                Log::info('Client review milestone email queued', [
                    'user_id' => $user->id,
                    'milestone' => $approvedReviews,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to queue client engagement email', [
                    'user_id' => $user->id,
                    'approved_reviews' => $approvedReviews,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    /**
     * Evaluate a provider's received approved-review count (1st / 5th milestone) and queue matching email once.
     */
    private function handleProviderReviewMilestones(ServiceProvider $provider): void
    {
        $provider->loadMissing('user');

        $providerEmail = $provider->user?->email;
        if (!$providerEmail) {
            return;
        }

        $receivedCount = $provider->reviews()->where('is_active', true)->count();

        $flagColumn = self::PROVIDER_MILESTONES[$receivedCount] ?? null;
        if ($flagColumn === null) {
            return; // Not a milestone count.
        }

        // Stamp the flag immediately.
        $updated = ServiceProvider::whereKey($provider->id)
            ->whereNull($flagColumn)
            ->update([$flagColumn => now()]);

        if ($updated === 0) {
            return; // Already sent for this milestone.
        }

        DB::afterCommit(function () use ($provider, $providerEmail, $receivedCount) {
            try {
                $profileUrl = route('service-providers.show', $provider->id) . '#reviews';

                $mailable = $receivedCount >= 5
                    ? new FifthReviewReceivedEmail($provider, $profileUrl)
                    : new FirstReviewReceivedEmail($provider, $profileUrl);

                Mail::to($providerEmail)->queue($mailable);

                Log::info('Provider received-review milestone email queued', [
                    'provider_id' => $provider->id,
                    'milestone' => $receivedCount,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to queue provider review milestone email', [
                    'provider_id' => $provider->id,
                    'received_reviews' => $receivedCount,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }
}
