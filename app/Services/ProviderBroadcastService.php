<?php

namespace App\Services;

use App\Jobs\SendProviderBroadcastEmail;
use App\Models\ProviderBroadcast;
use App\Models\ProviderBroadcastRecipient;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * Builds and dispatches an admin broadcast to service providers.
 *
 * The send is deliberately split into two phases:
 *
 *   1. Materialise the audience into provider_broadcast_recipients, inside a
 *      transaction. After this the recipient list is fixed — a provider who
 *      signs up mid-send is not silently included, and the unique index makes
 *      a second trigger a no-op instead of a duplicate mailing.
 *
 *   2. Dispatch one queued job per recipient. One job per recipient (rather
 *      than one job for the whole list) means a single bad address cannot
 *      abort the run, each failure is retried and recorded on its own row, and
 *      a queue restart mid-send resumes exactly where it stopped.
 */
class ProviderBroadcastService
{
    /**
     * Providers eligible to receive a broadcast.
     *
     * Excludes: inactive providers, providers whose user account is disabled or
     * deleted, blank/duplicate addresses, and anyone who has unsubscribed.
     * Bounces from dead accounts are the fastest way to wreck a sending
     * domain's reputation, so the filter is intentionally strict.
     */
    public function recipientQuery(): Builder
    {
        return ServiceProvider::query()
            ->where('is_active', true)
            ->whereHas('user', function ($query) {
                $query->where('is_active', true)
                    ->whereNull('broadcast_opt_out_at')
                    ->whereNotNull('email')
                    ->where('email', '!=', '');
            })
            ->with('user:id,name,email');
    }

    public function audienceCount(): int
    {
        return $this->recipientQuery()->count();
    }

    /**
     * Freeze the audience and queue the send.
     *
     * @return int number of recipients queued
     */
    public function queue(ProviderBroadcast $broadcast): int
    {
        if (!$broadcast->isSendable()) {
            return 0;
        }

        $rows = [];
        $seenEmails = [];
        $now = now();

        $this->recipientQuery()->chunkById(500, function ($providers) use (&$rows, &$seenEmails, $broadcast, $now) {
            foreach ($providers as $provider) {
                $email = mb_strtolower(trim((string) $provider->user?->email));

                if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                // Two providers sharing one login would otherwise get the same
                // email twice.
                if (isset($seenEmails[$email])) {
                    continue;
                }
                $seenEmails[$email] = true;

                $rows[] = [
                    'provider_broadcast_id' => $broadcast->id,
                    'service_provider_id' => $provider->id,
                    'email' => $email,
                    'name' => $provider->company_name ?: $provider->user?->name,
                    'status' => ProviderBroadcastRecipient::STATUS_PENDING,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        });

        if ($rows === []) {
            return 0;
        }

        DB::transaction(function () use ($rows, $broadcast) {
            foreach (array_chunk($rows, 500) as $chunk) {
                // insertOrIgnore leans on broadcast_provider_unique: re-running
                // this can never create a duplicate mailing.
                ProviderBroadcastRecipient::insertOrIgnore($chunk);
            }

            $broadcast->forceFill([
                'status' => ProviderBroadcast::STATUS_QUEUED,
                'queued_at' => now(),
                'recipients_total' => $broadcast->recipients()->count(),
                'sent_count' => 0,
                'failed_count' => 0,
            ])->save();
        });

        // Dispatched after the transaction commits so a worker can never pick
        // up a job before its recipient row is visible.
        $broadcast->recipients()->pending()->select('id')->chunkById(500, function ($recipients) {
            foreach ($recipients as $recipient) {
                SendProviderBroadcastEmail::dispatch($recipient->id);
            }
        });

        return (int) $broadcast->fresh()->recipients_total;
    }

    /**
     * Signed, per-user opt-out link. Signing means the URL cannot be edited to
     * unsubscribe somebody else, and needs no login to work from an inbox.
     */
    public function unsubscribeUrl(User $user): string
    {
        return URL::signedRoute('broadcast.unsubscribe', ['user' => $user->id]);
    }

    /**
     * Where the email's default CTA points.
     */
    public function dashboardUrl(?ServiceProvider $provider = null): string
    {
        if ($provider) {
            return route('service-providers.show', $provider->id);
        }

        return route('home');
    }
}
