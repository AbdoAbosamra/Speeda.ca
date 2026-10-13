<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only, privacy-safe log behind the offer analytics.
 *
 * No IP address and no raw User-Agent: a salted session hash for unique
 * counts, a coarse device bucket, the locale and where the visitor came from.
 */
class OfferEvent extends Model
{
    public const UPDATED_AT = null;

    public const IMPRESSION = 'impression';
    public const VIEW = 'view';
    /** "Get this offer on WhatsApp" was pressed. */
    public const CLAIM = 'claim';
    public const SHARE = 'share';
    public const LIKE = 'like';
    public const FAVORITE = 'favorite';
    public const COMMENT = 'comment';

    public const EVENTS = [
        self::IMPRESSION, self::VIEW, self::CLAIM, self::SHARE,
        self::LIKE, self::FAVORITE, self::COMMENT,
    ];

    /** Where a visitor met the offer. Anything else is stored as "direct". */
    public const SOURCES = ['home', 'offers', 'profile', 'listing', 'similar', 'share', 'direct'];

    protected $fillable = [
        'offer_id',
        'service_provider_id',
        'event',
        'source',
        'session_hash',
        'user_id',
        'device_type',
        'locale',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class)->withTrashed();
    }

    public static function normalizeSource(?string $source): string
    {
        return in_array($source, self::SOURCES, true) ? $source : 'direct';
    }
}
