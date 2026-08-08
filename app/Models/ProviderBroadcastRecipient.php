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
