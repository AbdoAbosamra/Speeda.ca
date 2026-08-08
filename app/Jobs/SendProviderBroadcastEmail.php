<?php

namespace App\Jobs;

use App\Mail\Provider\BroadcastEmail;
use App\Models\ProviderBroadcastRecipient;
use App\Services\ProviderBroadcastService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Delivers one broadcast to one provider.
 *
 * Scoped to a single recipient row on purpose: a bad address, a rejected
 * message, or a worker restart affects only that provider. Everyone else's
 * job is untouched, and the ledger row records precisely what happened.
 */
class SendProviderBroadcastEmail implements ShouldQueue
{
    use Queueable;

    /** Retries spread over ~a minute, for transient SMTP failures. */
    public int $tries = 3;

    public array $backoff = [10, 30];

    public function __construct(public readonly int $recipientId) {}

    /**
     * Guard against the same recipient being queued twice: the job is a no-op
     * unless the row is still pending.
     */
    public function handle(ProviderBroadcastService $service): void
    {
        $recipient = ProviderBroadcastRecipient::with(['broadcast', 'serviceProvider.user'])
            ->find($this->recipientId);

        if (!$recipient || $recipient->status !== ProviderBroadcastRecipient::STATUS_PENDING) {
            return;
        }

        $broadcast = $recipient->broadcast;
        $provider = $recipient->serviceProvider;
        $user = $provider?->user;

        if (!$broadcast || !$user) {
            $recipient->markFailed('Provider or user record no longer exists.');
            $broadcast?->refreshProgress();

            return;
        }

        // Re-checked at send time, not just at queue time: someone who
        // unsubscribes while a large send is draining must not still be mailed.
        if ($user->broadcast_opt_out_at !== null) {
            $recipient->markFailed('Recipient unsubscribed before delivery.');
            $broadcast->refreshProgress();

            return;
        }

        try {
            Mail::to($recipient->email, $recipient->name)->send(new BroadcastEmail(
                broadcast: $broadcast,
                recipientName: $recipient->name ?: $user->name,
                dashboardUrl: $service->dashboardUrl($provider),
                unsubscribeUrl: $service->unsubscribeUrl($user),
            ));

            $recipient->markSent();
        } catch (Throwable $e) {
            // Let the queue retry first; only record a terminal failure once
            // the attempts are exhausted.
            if ($this->attempts() < $this->tries) {
                throw $e;
            }

            $recipient->markFailed($e->getMessage());

            Log::error('[Broadcast] Delivery failed', [
                'broadcast_id' => $broadcast->id,
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage(),
            ]);
        }

        $broadcast->refreshProgress();
    }

    /**
     * Reached when the job itself dies (timeout, worker kill) rather than the
     * send throwing — without this the row would sit pending forever and the
     * broadcast would never close.
     */
    public function failed(?Throwable $e): void
    {
        $recipient = ProviderBroadcastRecipient::with('broadcast')->find($this->recipientId);

        if (!$recipient || $recipient->status !== ProviderBroadcastRecipient::STATUS_PENDING) {
            return;
        }

        $recipient->markFailed($e?->getMessage() ?: 'Job failed without an exception message.');
        $recipient->broadcast?->refreshProgress();
    }
}
