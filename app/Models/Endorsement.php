<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Endorsement Model - Professional "Recommend" feature
 * 
 * Following SPEEDA V5.0 architecture:
 * - Uses service_provider_id (not legacy profile_id)
 * - Only clients can endorse providers
 * - One endorsement per user per provider
 */
class Endorsement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'service_provider_id',
        'user_id',
    ];

    /**
     * Get the service provider being endorsed.
     */
    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    /**
     * Get the user who made the endorsement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
