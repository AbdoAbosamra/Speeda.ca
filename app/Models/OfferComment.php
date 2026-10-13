<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * A visitor's comment on an offer. Nothing is public until an admin approves
 * it; rejected comments stay stored so the admin can change their mind.
 */
class OfferComment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUSES = [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED];

    protected $fillable = [
        'offer_id',
        'user_id',
        'author_name',
        'body',
        'status',
        'locale',
        'session_hash',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * The admin top bar and dashboard cache the pending count; any new,
     * moderated or deleted comment changes it.
     */
    protected static function booted(): void
    {
        $invalidate = function () {
            Cache::forget('admin_pending_offer_comments');
            Cache::forget('admin_dash_offers');
        };
        static::saved($invalidate);
        static::deleted($invalidate);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function initial(): string
    {
        return mb_strtoupper(mb_substr(trim($this->author_name), 0, 1)) ?: '?';
    }

    /**
     * Keeps offers.comments_count equal to the number of approved comments.
     */
    public static function syncCount(int $offerId): void
    {
        $count = static::where('offer_id', $offerId)->approved()->count();

        Offer::withoutTimestamps(fn () => Offer::withTrashed()->whereKey($offerId)->update(['comments_count' => $count]));
    }
}
