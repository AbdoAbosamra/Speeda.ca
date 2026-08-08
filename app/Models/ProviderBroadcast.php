<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A one-off email an admin wrote and sent to service providers.
 *
 * Unlike EmailTemplate (which overrides the copy of the *automated* journey
 * emails), a broadcast is authored from scratch in the dashboard, sent once,
 * and then frozen as a historical record of what went out and to whom.
 */
class ProviderBroadcast extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';

    protected $fillable = [
        'subject',
        'preheader',
        'body',
        'cta_label',
        'cta_url',
        'status',
        'created_by',
        'queued_at',
        'sent_at',
        'recipients_total',
        'sent_count',
        'failed_count',
    ];

    protected $casts = [
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'recipients_total' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
    ];

    /* =====================================================================
     |  RELATIONSHIPS
     * ===================================================================== */

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(ProviderBroadcastRecipient::class);
    }

    /* =====================================================================
     |  STATE
     * ===================================================================== */

    /**
     * Only drafts may be edited or deleted. Once a broadcast has been queued
     * the copy is what recipients already received, so it becomes read-only —
     * editing it would silently rewrite history.
     */
    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSendable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isFinished(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function scopeDrafts(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Percentage of recipients that reached a terminal state (sent or failed).
     */
    public function progressPercent(): int
    {
        if ($this->recipients_total < 1) {
            return $this->isFinished() ? 100 : 0;
        }

        $done = $this->sent_count + $this->failed_count;

        return (int) min(100, round($done / $this->recipients_total * 100));
    }

    /**
     * Recalculate counters from the ledger and close the broadcast once every
     * recipient is done. Called after each recipient job so the dashboard is
     * accurate while a large send is still draining the queue.
     */
    public function refreshProgress(): void
    {
        $counts = $this->recipients()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $sent = (int) ($counts[ProviderBroadcastRecipient::STATUS_SENT] ?? 0);
        $failed = (int) ($counts[ProviderBroadcastRecipient::STATUS_FAILED] ?? 0);
        $total = (int) $counts->sum();

        $attributes = [
            'recipients_total' => $total,
            'sent_count' => $sent,
            'failed_count' => $failed,
        ];

        if ($total > 0 && ($sent + $failed) >= $total) {
            $attributes['status'] = self::STATUS_SENT;
            $attributes['sent_at'] = $this->sent_at ?: now();
        } elseif ($this->status === self::STATUS_QUEUED) {
            $attributes['status'] = self::STATUS_SENDING;
        }

        $this->forceFill($attributes)->save();
    }

    /**
     * Placeholders an admin may type into the subject or body.
     */
    public const PLACEHOLDERS = [
        'provider_name' => 'The provider / business name',
        'dashboard_url' => 'Link to the provider dashboard',
        'site_name' => 'Speeda',
    ];
}
