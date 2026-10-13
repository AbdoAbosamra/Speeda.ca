<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One like on an offer: by an account, or by a guest's salted session hash.
 */
class OfferLike extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['offer_id', 'user_id', 'session_hash', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }
}
