<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'user_id',
        'content',
        'is_active',
        'is_flagged',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_flagged' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the parent commentable model (polymorphic).
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who wrote this comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved this comment.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope to get only active (approved) comments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only pending (unapproved) comments.
     */
    public function scopePending($query)
    {
        return $query->where('is_active', false)->whereNull('rejection_reason');
    }

    /**
     * Scope to get only flagged comments.
     */
    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }

    /**
     * Scope to get only rejected comments.
     */
    public function scopeRejected($query)
    {
        return $query->whereNotNull('rejection_reason');
    }

    /**
     * Approve a comment (make it active/visible).
     */
    public function approve(User $admin): void
    {
        if (!$admin->isAdmin()) {
            throw new \Exception('Only admins can approve comments');
        }

        $this->update([
            'is_active' => true,
            'is_flagged' => false,
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    /**
     * Reject a comment with optional reason.
     */
    public function reject(User $admin, ?string $reason = null): void
    {
        if (!$admin->isAdmin()) {
            throw new \Exception('Only admins can reject comments');
        }

        $this->update([
            'is_active' => false,
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Flag a comment for admin review.
     */
    public function flag(): void
    {
        $this->update(['is_flagged' => true]);
    }

    /**
     * Unflag a comment.
     */
    public function unflag(): void
    {
        $this->update(['is_flagged' => false]);
    }
}
