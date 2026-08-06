<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\User;
use App\Helpers\ErrorHelper;
use App\Traits\HandlesBulkActions;
use App\Traits\LogsAdminActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminCommentController extends Controller
{
    use LogsAdminActions;
    use HandlesBulkActions;

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, 'comments');
    }

    protected function bulkActions(string $resource): array
    {
        return [
            'approve' => __('admin.bulk_verb_approved'),
            'reject' => __('admin.bulk_verb_rejected'),
            'flag' => __('admin.bulk_verb_flagged'),
            'unflag' => __('admin.bulk_verb_unflagged'),
            'delete' => __('admin.bulk_verb_deleted'),
            'restore' => __('admin.bulk_verb_restored'),
        ];
    }

    protected function bulkQuery(string $resource): Builder
    {
        // withTrashed so the Trash tab can restore in bulk; `user` is eager
        // loaded because approve()/reject() and the audit log touch it.
        return Comment::withTrashed()->with('user');
    }

    /**
     * @return true|string
     */
    protected function applyBulkAction(string $resource, string $action, $comment)
    {
        /** @var \App\Models\User $admin */
        $admin = Auth::user();

        // Moderation actions do not apply to trashed rows — restore first.
        if ($comment->trashed() && $action !== 'restore') {
            return __('admin.bulk_reason_already_trashed');
        }

        return match ($action) {
            'approve' => $this->bulkApprove($comment, $admin),
            'reject' => $this->bulkReject($comment, $admin),
            'flag' => $this->bulkFlag($comment),
            'unflag' => $this->bulkUnflag($comment),
            'delete' => $this->bulkDelete($comment),
            'restore' => $this->bulkRestore($comment),
            default => __('admin.bulk_reason_failed'),
        };
    }

    private function bulkApprove(Comment $comment, $admin)
    {
        if ($comment->is_active) {
            return __('admin.bulk_reason_already_approved');
        }

        $comment->approve($admin);
        $this->logApprove($comment);

        return true;
    }

    private function bulkReject(Comment $comment, $admin)
    {
        if ($comment->isRejected()) {
            return __('admin.bulk_reason_already_rejected');
        }

        $comment->reject($admin);
        $this->logReject($comment);

        return true;
    }

    private function bulkFlag(Comment $comment)
    {
        if ($comment->is_flagged) {
            return __('admin.bulk_reason_already_flagged');
        }

        $comment->flag();
        $this->logAction('flag', $comment);

        return true;
    }

    private function bulkUnflag(Comment $comment)
    {
        if (!$comment->is_flagged) {
            return __('admin.bulk_reason_not_flagged');
        }

        $comment->unflag();
        $this->logAction('unflag', $comment);

        return true;
    }

    private function bulkDelete(Comment $comment)
    {
        $this->logAction('delete', $comment, ['deleted' => $comment->toArray()]);
        $comment->delete();

        return true;
    }

    private function bulkRestore(Comment $comment)
    {
        if (!$comment->trashed()) {
            return __('admin.bulk_reason_not_trashed');
        }

        $comment->restore();
        $this->logAction('restore', $comment);

        return true;
    }

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
            $status = (string) $request->input('status', '');

            // "deleted" is the only view that reaches into the trash; every other
            // tab keeps the default (non-trashed) scope.
            $query = $status === 'deleted'
                ? Comment::onlyTrashed()
                : Comment::query();

            $query->with(['user', 'approvedBy'])->orderByDesc('created_at');

            match ($status) {
                'active' => $query->where('is_active', true),
                'pending' => $query->pending(),
                'flagged' => $query->flagged(),
                'rejected' => $query->rejected(),
                default => null,
            };

            // filled() (not has()) so an empty select value means "no filter"
            // rather than matching on an empty string.
            if ($request->filled('commentable_type')) {
                $query->where('commentable_type', $request->input('commentable_type'));
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            $comments = $query->paginate(20)->withQueryString();

            $stats = [
                'total' => Comment::count(),
                'pending' => Comment::pending()->count(),
                'approved' => Comment::where('is_active', true)->count(),
                'flagged' => Comment::flagged()->count(),
                'rejected' => Comment::rejected()->count(),
                'deleted' => Comment::onlyTrashed()->count(),
            ];

            return view('admin.comments.index', compact('comments', 'stats', 'status'));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->route('admin.dashboard');
        }
    }

    /**
     * Show comment details for admin review/approval.
     */
    public function show(int $commentId)
    {
        try {
            // withTrashed so a soft-deleted comment can still be inspected and
            // restored from its detail page.
            $comment = Comment::withTrashed()
                ->with(['user', 'approvedBy', 'commentable'])
                ->findOrFail($commentId);

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
                $this->logApprove($comment);

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
                $this->logReject($comment, $reason);

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
            $this->logAction('delete', $comment, ['deleted' => $comment->toArray()]);
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
