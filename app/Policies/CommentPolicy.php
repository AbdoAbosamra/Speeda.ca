<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Comment;

class CommentPolicy
{
    /**
     * Determine whether the user can view any comments.
     */
    public function viewAny(User $user): bool
    {
        return true; // Anyone can view published comments
    }

    /**
     * Determine whether the user can view the comment.
     */
    public function view(User $user, Comment $comment): bool
    {
        // Can view if comment is published or if user is the creator or admin
        if ($comment->is_active) {
            return true;
        }

        return $user->id === $comment->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can create comments.
     */
    public function create(User $user): bool
    {
        // Authenticated users can create comments
        return true;
    }

    /**
     * Determine whether the user can update the comment.
     */
    public function update(User $user, Comment $comment): bool
    {
        // Only the comment author can edit their own unpublished comments
        if ($user->id !== $comment->user_id) {
            return false;
        }

        // Cannot edit comments that are already approved
        return !$comment->is_active;
    }

    /**
     * Determine whether the user can delete the comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // Only the comment author or admin can delete
        return $user->id === $comment->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can approve comments (admin only).
     */
    public function approve(User $user, Comment $comment): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can reject comments (admin only).
     */
    public function reject(User $user, Comment $comment): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can flag comments.
     */
    public function flag(User $user, Comment $comment): bool
    {
        // Users can flag comments but not their own
        return $user->id !== $comment->user_id;
    }

    /**
     * Determine whether the user can unflag comments (admin only).
     */
    public function unflag(User $user, Comment $comment): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore soft-deleted comments (admin only).
     */
    public function restore(User $user, Comment $comment): bool
    {
        return $user->isAdmin();
    }
}
