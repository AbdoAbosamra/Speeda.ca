<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The admin decision on a provider-submitted offer.
 *
 *   pending  → approved | rejected
 *   rejected → approved            (the admin changed their mind)
 *   rejected → pending             (the provider edited and resubmitted)
 *
 * Approval publishes the offer (dates still apply). Each decision leaves the
 * provider an in-app notification so they do not have to keep checking.
 */
class OfferReview
{
    public const APPROVE = 'approved';
    public const REJECT = 'rejected';

    /**
     * Apply the decision; returns false when the offer's state no longer
     * allows it (someone else decided first, or it is already approved).
     */
    public function decide(Offer $offer, string $decision, User $admin, ?string $reason = null, bool $featured = false): bool
    {
        $decided = DB::transaction(function () use ($offer, $decision, $admin, $reason, $featured) {
            $locked = Offer::whereKey($offer->id)->lockForUpdate()->first();
            if (! $locked || ! $this->allowed($locked, $decision)) {
                return null;
            }

            $approved = $decision === self::APPROVE;
            $locked->update([
                'review_status' => $decision,
                'reviewed_at' => now(),
                'reviewed_by' => $admin->id,
                'updated_by' => $admin->id,
                'rejection_reason' => $approved ? null : trim((string) $reason),
                'is_active' => $approved,
                'is_featured' => $approved && $featured,
            ]);

            return $locked;
        });

        if (! $decided) {
            return false;
        }

        $offer->setRawAttributes($decided->getAttributes(), true);
        $this->notifyProvider($decided, $admin);

        return true;
    }

    public function allowed(Offer $offer, string $decision): bool
    {
        return match ($decision) {
            self::APPROVE => in_array($offer->review_status, [Offer::STATUS_PENDING, Offer::STATUS_REJECTED], true),
            self::REJECT => $offer->review_status === Offer::STATUS_PENDING,
            default => false,
        };
    }

    /**
     * A bell notification for this provider only. Skipped (never broadcast)
     * when per-provider targeting is unavailable.
     */
    private function notifyProvider(Offer $offer, User $admin): void
    {
        $provider = $offer->serviceProvider;
        if (! $provider || ! AdminNotification::supportsProviderTargeting()) {
            return;
        }

        try {
            $approved = $offer->review_status === self::APPROVE;
            $key = $approved ? 'offer_submission.notify_approved' : 'offer_submission.notify_rejected';
            $fields = [];
            foreach (['ar', 'en', 'fr'] as $locale) {
                $title = $offer->{'title_'.$locale} ?: $offer->title_en;
                $fields['title_'.$locale] = __($key.'_title', [], $locale);
                $fields['message_'.$locale] = __($key.'_message', ['title' => $title, 'reason' => (string) $offer->rejection_reason], $locale);
            }

            DB::transaction(function () use ($fields, $admin, $provider) {
                $notification = AdminNotification::create($fields + [
                    'target_type' => 'provider_only',
                    'created_by' => $admin->id,
                    'expires_at' => now()->addDays(30),
                ]);
                $notification->targetServiceProviders()->sync([$provider->id]);
            });

            Cache::forget("nav_notifications_{$provider->user_id}");
        } catch (\Throwable $e) {
            // The decision is saved; a missing notification must not undo it.
            Log::warning('Offer review notification failed', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
        }
    }
}
