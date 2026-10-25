<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per provider a broadcast was addressed to.
 *
 * This is the delivery ledger. It exists so that a send which is interrupted
 * (queue restart, deploy, mail server hiccup) can be resumed without guessing,
 * and so an admin can see exactly who received the email and who did not.
 */
class ProviderBroadcastRecipient extends Model
{
    public const STATUS_PENDING = 'pending';
    /** Claimed by a job that is talking to the mail server right now. */
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'provider_broadcast_id',
        'service_provider_id',
        'email',
        'name',
        'status',
        'error',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(ProviderBroadcast::class, 'provider_broadcast_id');
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Atomically move a pending row to "sending". Only the caller that wins
     * this update may talk to the mail server, so the same row can never be
     * mailed twice, whatever happens to the job afterwards.
     */
    public function claimForSending(): bool
    {
        return static::whereKey($this->getKey())
            ->where('status', self::STATUS_PENDING)
            ->update(['status' => self::STATUS_SENDING, 'updated_at' => now()]) === 1;
    }

    /**
     * Put a claimed row back in the queue after a send that failed before the
     * mail server accepted anything, so the next attempt may try again.
     */
    public function releaseClaim(): void
    {
        static::whereKey($this->getKey())
            ->where('status', self::STATUS_SENDING)
            ->update(['status' => self::STATUS_PENDING, 'updated_at' => now()]);
    }

    /**
     * Close a row whose send was interrupted (worker killed, timeout, deploy
     * restart) after it was claimed. Whether the mail server accepted the
     * message is unknown, so it is recorded as failed and NOT retried: a
     * possibly missed email is better than a possibly duplicated one.
     */
    public function closeInterruptedSend(): void
    {
        static::whereKey($this->getKey())
            ->where('status', self::STATUS_SENDING)
            ->update([
                'status' => self::STATUS_FAILED,
                'error' => 'Delivery interrupted mid-send; not retried to avoid a duplicate email.',
                'updated_at' => now(),
            ]);
    }

    public function markSent(): void
    {
        $this->forceFill([
            'status' => self::STATUS_SENT,
            'error' => null,
            'sent_at' => now(),
        ])->save();
    }

    public function markFailed(string $reason): void
    {
        $this->forceFill([
            'status' => self::STATUS_FAILED,
            // Column is TEXT; trim defensively so a huge SMTP dump cannot
            // blow up the insert.
            'error' => mb_substr($reason, 0, 2000),
        ])->save();
    }
}
