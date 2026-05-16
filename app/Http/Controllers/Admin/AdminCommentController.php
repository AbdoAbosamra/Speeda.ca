<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\User;
use App\Helpers\ErrorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display all comments (approved, pending, flagged, rejected) in admin panel.
     */
    public function index(Request $request)
    {
        try {
            $query = Comment::with(['user', 'approvedBy'])
                ->orderByDesc('created_at');

            // Filter by status
            if ($request->get('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->get('status') === 'pending') {
                $query->pending();
            } elseif ($request->get('status') === 'flagged') {
                $query->flagged();
            } elseif ($request->get('status') === 'rejected') {
                $query->rejected();
            }

            // Filter by commentable type
            if ($request->has('commentable_type')) {
                $query->where('commentable_type', $request->get('commentable_type'));
            }

            // Filter by user
            if ($request->has('user_id')) {
                $query->where('user_id', $request->get('user_id'));
            }

            $comments = $query->paginate(20)->withQueryString();

            return view('admin.comments.index', compact('comments'));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->route('admin.dashboard');
        }
    }

    /**
     * Show comment details for admin review/approval.
     */
    public function show(Comment $comment)
    {
        try {
            $comment->load(['user', 'approvedBy', 'commentable']);
            return view('admin.comments.show', compact('comment'));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->route('admin.comments');
        }
    }

    /**
     * Approve a comment (make it active/visible).
     */
    public function approve(Comment $comment)
    {
        try {
            /** @var User $admin */
            $admin = Auth::user();

            if (!$admin->isAdmin()) {
                abort(403, 'Only admins can approve comments');
            }

            return DB::transaction(function () use ($comment, $admin) {
                $comment->approve($admin);

                Log::info('Comment approved by admin', [
                    'comment_id' => $comment->id,
                    'admin_id' => $admin->id,
                    'user_id' => $comment->user_id,
                ]);

                ErrorHelper::flashNotification(
                    __('admin.comment_approved_successfully'),
                    'success'
                );

                return redirect()->route('admin.comments');
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Reject a comment with optional reason.
     */
    public function reject(Comment $comment, Request $request)
    {
        try {
            /** @var User $admin */
            $admin = Auth::user();

            if (!$admin->isAdmin()) {
                abort(403, 'Only admins can reject comments');
            }

            $reason = $request->input('reason');

            return DB::transaction(function () use ($comment, $admin, $reason) {
                $comment->reject($admin, $reason);

                Log::info('Comment rejected by admin', [
                    'comment_id' => $comment->id,
                    'admin_id' => $admin->id,
                    'user_id' => $comment->user_id,
                    'reason' => $reason,
                ]);

                ErrorHelper::flashNotification(
                    __('admin.comment_rejected_successfully'),
                    'success'
                );

                return redirect()->route('admin.comments');
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Mark a comment as flagged for admin review.
     */
    public function flag(Comment $comment)
    {
        try {
            $comment->flag();

            Log::info('Comment flagged for admin review', [
                'comment_id' => $comment->id,
            ]);

            ErrorHelper::flashNotification(
                __('admin.comment_flagged_successfully'),
                'success'
            );

            return redirect()->back();
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Remove flagged status from a comment.
     */
    public function unflag(Comment $comment)
    {
        try {
            $comment->unflag();

            Log::info('Comment unflagged', [
                'comment_id' => $comment->id,
            ]);

            ErrorHelper::flashNotification(
                __('admin.comment_unflagged_successfully'),
                'success'
            );

            return redirect()->back();
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Permanently delete a comment (soft delete to preserve history).
     */
    public function delete(Comment $comment)
    {
        try {
            $commentId = $comment->id;
            $comment->delete();

            Log::info('Comment deleted by admin', [
                'comment_id' => $commentId,
            ]);

            ErrorHelper::flashNotification(
                __('admin.comment_deleted_successfully'),
                'success'
            );

            return redirect()->route('admin.comments');
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Restore a soft-deleted comment.
     */
    public function restore(int $commentId)
    {
        try {
            $comment = Comment::withTrashed()->findOrFail($commentId);
            $comment->restore();

            Log::info('Comment restored by admin', [
                'comment_id' => $commentId,
            ]);

            ErrorHelper::flashNotification(
                __('admin.comment_restored_successfully'),
                'success'
            );

            return redirect()->route('admin.comments');
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }
}
