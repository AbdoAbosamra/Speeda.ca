<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Review;

class ReviewPolicy
{
    /**
     * Determine whether the user can view any reviews.
     */
    public function viewAny(User $user): bool
    {
        return true; // Anyone can view published reviews
    }

    /**
     * Determine whether the user can view the review.
     */
    public function view(User $user, Review $review): bool
    {
        // Can view if review is published or if user is the creator or admin
        if ($review->is_active) {
            return true;
        }

        return $user->id === $review->client_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can create reviews.
     */
    public function create(User $user): bool
    {
        // Only clients can create reviews
        return $user->user_type === 'client';
    }

    /**
     * Determine whether the user can update the review.
     */
    public function update(User $user, Review $review): bool
    {
        // Only the review author can edit their own unpublished reviews
        if ($user->id !== $review->client_id) {
            return false;
        }

        // Cannot edit reviews that are already approved
        return !$review->is_active;
    }

    /**
     * Determine whether the user can delete the review.
     */
    public function delete(User $user, Review $review): bool
    {
        // Only the review author or admin can delete
        return $user->id === $review->client_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can approve reviews (admin only).
     */
    public function approve(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can reject reviews (admin only).
     */
    public function reject(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can feature reviews (admin only).
     */
    public function feature(User $user, Review $review): bool
    {
        return $user->isAdmin();
    }
}
