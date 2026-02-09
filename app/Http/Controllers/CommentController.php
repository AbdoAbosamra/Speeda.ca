<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Review;
use App\Models\User;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Helpers\ErrorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    /**
     * Display all active (approved) comments for a reviewable item (e.g., Review).
     */
    public function index(Request $request)
    {
        try {
            $commentable_type = $request->get('commentable_type'); // e.g., "App\Models\Review"
            $commentable_id = $request->get('commentable_id');

            if (!$commentable_type || !$commentable_id) {
                ErrorHelper::flashNotification(__('comments.missing_parameters'), 'error');
                return redirect()->back();
            }

            // Only return active (approved) comments to public
            $comments = Comment::where('commentable_type', $commentable_type)
                ->where('commentable_id', $commentable_id)
                ->where('is_active', true)
                ->with(['user'])
                ->orderByDesc('created_at')
                ->paginate(10);

            return view('comments.index', compact('comments', 'commentable_type', 'commentable_id'));
        } catch (\Exception $e) {
            ErrorHelper::flashNotification($e->getMessage(), 'error');
            return redirect()->back();
        }
    }

    /**
     * Show comment creation form
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', __('comments.must_login_to_comment'));
        }

        if (!$user->isClient()) {
            return redirect()->back()->with('error', __('comments.only_clients_can_comment'));
        }

        $commentable_type = $request->get('commentable_type');
        $commentable_id = $request->get('commentable_id');

        if (!$commentable_type || !$commentable_id) {
            return redirect()->back()->with('error', __('comments.missing_parameters'));
        }

        return view('comments.create', compact('commentable_type', 'commentable_id'));
    }

    /**
     * Store a new comment - STRICT: Only authenticated users can comment.
     */
    public function store(StoreCommentRequest $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if (!$user) {
                return redirect()->route('login')->with('error', __('comments.must_login_to_comment'));
            }

            if (!$user->isClient()) {
                return redirect()->back()->with('error', __('comments.only_clients_can_comment'));
            }

            $validated = $request->validated();

            return DB::transaction(function () use ($user, $validated) {
                $comment = Comment::create([
                    'commentable_type' => $validated['commentable_type'],
                    'commentable_id' => $validated['commentable_id'],
                    'user_id' => $user->id,
                    'content' => $validated['content'],
                    'is_active' => false, // Must be approved by admin before showing
                    'is_flagged' => false,
                ]);

                Log::info('Comment created by user', [
                    'comment_id' => $comment->id,
                    'user_id' => $user->id,
                    'commentable_type' => $validated['commentable_type'],
                    'commentable_id' => $validated['commentable_id'],
                ]);

                // Redirect back to the review or item where comment was added
                return redirect()->back()
                    ->with('success', __('comments.comment_submitted_pending_approval'));
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            Log::warning('Comment creation failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', $error['message']);
        }
    }

    /**
     * Show comment edit form
     */
    public function edit(Comment $comment)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', __('comments.must_login_to_comment'));
        }

        // STRICT: Only the original author can edit
        if ($comment->user_id !== $user->id) {
            return redirect()->back()->with('error', __('comments.cannot_edit_others_comments'));
        }

        // STRICT: Cannot edit comments already approved
        if ($comment->is_active) {
            return redirect()->back()->with('error', __('comments.cannot_edit_approved_comments'));
        }

        return view('comments.edit', compact('comment'));
    }

    /**
     * Update a comment - STRICT: Only the ORIGINAL AUTHOR can update their own comment before admin approval.
     */
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // STRICT: Only the original author can edit their comment
            if ($comment->user_id !== $user->id) {
                return redirect()->back()->with('error', __('comments.cannot_edit_others_comments'));
            }

            // STRICT: Cannot edit comments that are already active (approved by admin)
            if ($comment->is_active) {
                return redirect()->back()->with('error', __('comments.cannot_edit_approved_comments'));
            }

            $validated = $request->validated();

            return DB::transaction(function () use ($comment, $user, $validated) {
                $comment->update([
                    'content' => $validated['content'] ?? $comment->content,
                    'is_active' => false, // Reset approval status on edit
                ]);

                Log::info('Comment updated by user', [
                    'comment_id' => $comment->id,
                    'user_id' => $user->id,
                ]);

                return redirect()->back()
                    ->with('success', __('comments.comment_updated_pending_reapproval'));
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            return redirect()->back()->with('error', $error['message']);
        }
    }

    /**
     * Delete a comment - STRICT: Only author OR admin can delete.
     */
    public function destroy(Comment $comment)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // STRICT: Only the author or admin can delete
            if ($comment->user_id !== $user->id && !$user->isAdmin()) {
                return redirect()->back()->with('error', __('comments.not_authorized_to_delete'));
            }

            $commentId = $comment->id;
            $comment->delete();

            Log::info('Comment deleted', [
                'comment_id' => $commentId,
                'deleted_by_user_id' => $user->id,
                'deleted_by_role' => $user->role,
            ]);

            return redirect()->back()
                ->with('success', __('comments.comment_deleted_successfully'));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            return redirect()->back()->with('error', $error['message']);
        }
    }

    /**
     * Flag a comment for admin review (user reports inappropriate content).
     */
    public function flag(Comment $comment)
    {
        try {
            if ($comment->is_flagged) {
                return redirect()->back()->with('error', __('comments.comment_already_flagged'));
            }

            $comment->flag();

            Log::info('Comment flagged by user', [
                'comment_id' => $comment->id,
            ]);

            return redirect()->back()
                ->with('success', __('comments.comment_flagged_successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
