<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Review extends Model
{
    use HasFactory;

    protected $table = 'service_provider_reviews';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'service_provider_id',
        'client_id',
        'booking_id',
        'rating',
        'review_text',
        'rating_breakdown',
        'is_verified',
        'is_featured',
        'is_active',
        'admin_approved_by',
        'admin_approved_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'rating' => 'integer',
        'rating_breakdown' => 'array',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'admin_approved_at' => 'datetime',
    ];

    /**
     * Get the service provider that owns the review.
     */
    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    /**
     * Get the client (user) who wrote the review.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the admin who approved this review.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }

    /**
     * Get the booking this review is associated with.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get all comments on this review.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get only active (approved) comments on this review.
     */
    public function activeComments(): MorphMany
    {
        return $this->comments()->where('is_active', true)->latest();
    }

    /**
     * Scope to get only active (approved) reviews.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only verified reviews.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to get only featured reviews.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Approve a review (set as active by admin).
     */
    public function approve(User $admin): void
    {
        if (!$admin->isAdmin()) {
            throw new \Exception('Only admins can approve reviews');
        }

        $this->update([
            'is_active' => true,
            'admin_approved_by' => $admin->id,
            'admin_approved_at' => now(),
        ]);
    }

    /**
     * Reject a review (set as inactive).
     */
    public function reject(User $admin): void
    {
        if (!$admin->isAdmin()) {
            throw new \Exception('Only admins can reject reviews');
        }

        $this->update([
            'is_active' => false,
            'admin_approved_by' => $admin->id,
            'admin_approved_at' => now(),
        ]);
    }
}
