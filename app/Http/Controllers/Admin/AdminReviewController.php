<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use App\Helpers\ErrorHelper;
use App\Traits\HandlesBulkActions;
use App\Traits\LogsAdminActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminReviewController extends Controller
{
    use LogsAdminActions;
    use HandlesBulkActions;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display all reviews (approved and pending) in admin panel.
     */
    public function index(Request $request)
    {
        try {
            $query = Review::with(['client', 'serviceProvider.user', 'approvedBy'])
                ->orderByDesc('created_at');

            // Filter by status
            match ($request->input('status')) {
                'active' => $query->where('is_active', true),
                'pending' => $query->where('is_active', false)->whereNull('admin_approved_at'),
                'rejected' => $query->where('is_active', false)->whereNotNull('admin_approved_at'),
                'featured' => $query->where('is_featured', true),
                default => null,
            };

            // filled() (not has()) so an empty select value means "no filter"
            // rather than matching WHERE rating = ''.
            if ($request->filled('rating')) {
                $query->where('rating', (int) $request->input('rating'));
            }

            if ($request->filled('provider_id')) {
                $query->where('service_provider_id', (int) $request->input('provider_id'));
            }

            $allowedPerPage = [10, 25, 50, 100];
            $perPage = (int) $request->input('per_page', 25);
            if (!in_array($perPage, $allowedPerPage, true)) {
                $perPage = 25;
            }

            $reviews = $query->paginate($perPage)->withQueryString();

            // Get statistics for dashboard
            $stats = [
                'total' => Review::count(),
                'pending' => Review::where('is_active', false)->whereNull('admin_approved_at')->count(),
                'approved' => Review::where('is_active', true)->count(),
                'rejected' => Review::where('is_active', false)->whereNotNull('admin_approved_at')->count(),
                'featured' => Review::where('is_featured', true)->count(),
                'average_rating' => Review::where('is_active', true)->avg('rating') ?? 0,
            ];

            return view('admin.reviews.index', compact('reviews', 'stats', 'perPage'));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->route('admin.dashboard');
        }
    }

    /* =====================================================================
     |  BULK ACTIONS
     * ===================================================================== */

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, 'reviews');
    }

    protected function bulkActions(string $resource): array
    {
        return [
            'approve' => __('admin.bulk_verb_approved'),
            'reject' => __('admin.bulk_verb_rejected'),
            'feature' => __('admin.bulk_verb_featured'),
            'unfeature' => __('admin.bulk_verb_unfeatured'),
            'delete' => __('admin.bulk_verb_deleted'),
        ];
    }

    protected function bulkQuery(string $resource): Builder
    {
        // Review::approve()/reject() touch $this->client and recalculate the
        // provider rating, so those relations are eager loaded: it avoids an
        // N+1 across the batch and keeps the strict lazy-loading guard
        // (Model::preventLazyLoading in local/staging) from aborting each row.
        return Review::query()->with(['client', 'serviceProvider']);
    }

    /**
     * @return true|string
     */
    protected function applyBulkAction(string $resource, string $action, $review)
    {
        /** @var \App\Models\User $admin */
        $admin = Auth::user();

        return match ($action) {
            'approve' => $this->bulkApprove($review, $admin),
            'reject' => $this->bulkReject($review, $admin),
            'feature' => $this->bulkFeature($review),
            'unfeature' => $this->bulkUnfeature($review),
            'delete' => $this->bulkDelete($review),
            default => __('admin.bulk_reason_failed'),
        };
    }

    private function bulkApprove(Review $review, $admin)
    {
        if ($review->is_active) {
            return __('admin.bulk_reason_already_approved');
        }

        $review->approve($admin);
        $this->logApprove($review);

        return true;
    }

    private function bulkReject(Review $review, $admin)
    {
        if (!$review->is_active && $review->admin_approved_at) {
            return __('admin.bulk_reason_already_rejected');
        }

        $review->reject($admin);
        $this->logReject($review);

        return true;
    }

    private function bulkFeature(Review $review)
    {
        // Same guard as the single-item route: only published reviews may be
        // featured, otherwise a hidden review would surface on the profile.
        if (!$review->is_active) {
            return __('admin.bulk_reason_not_approved');
        }

        if ($review->is_featured) {
            return __('admin.bulk_reason_already_featured');
        }

        $review->update(['is_featured' => true]);
        $this->logAction('feature', $review);

        return true;
    }

    private function bulkUnfeature(Review $review)
    {
        if (!$review->is_featured) {
            return __('admin.bulk_reason_not_featured');
        }

        $review->update(['is_featured' => false]);
        $this->logAction('unfeature', $review);

        return true;
    }

    private function bulkDelete(Review $review)
    {
        $providerId = $review->service_provider_id;
        $wasPublished = (bool) $review->is_active;

        $this->logAction('delete', $review, ['deleted' => $review->toArray()]);
        $review->delete();

        // Deleting a published review changes the provider's average.
        if ($wasPublished && $providerId) {
            Review::recalculateProviderRating($providerId);
        }

        return true;
    }

    /**
     * Show review details for admin review/approval.
     */
    public function show(Review $review)
    {
        try {
            $review->load(['client', 'serviceProvider.user', 'approvedBy', 'booking']);
            return view('admin.reviews.show', compact('review'));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->route('admin.reviews');
        }
    }

    /**
     * Approve a review (make it active/visible on website).
     * Automatically recalculates provider rating.
     */
    public function approve(Review $review)
    {
        try {
            /** @var User $admin */
            $admin = Auth::user();

            if (!$admin->isAdmin()) {
                abort(403, 'Only admins can approve reviews');
            }

            return DB::transaction(function () use ($review, $admin) {
                // Call model's approve method which handles rating recalculation
                $review->approve($admin);
                $this->logApprove($review);

                Log::info('Review approved by admin', [
                    'review_id' => $review->id,
                    'admin_id' => $admin->id,
                    'client_id' => $review->client_id,
                    'provider_id' => $review->service_provider_id,
                ]);

                ErrorHelper::flashNotification(
                    'Review approved successfully',
                    'success'
                );

                return redirect()->route('admin.reviews');
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Reject a review (mark as inactive/hidden).
     */
    public function reject(Review $review, Request $request)
    {
        try {
            /** @var User $admin */
            $admin = Auth::user();

            if (!$admin->isAdmin()) {
                abort(403, 'Only admins can reject reviews');
            }

            // Optional: Get rejection reason
            $reason = $request->input('reason');

            return DB::transaction(function () use ($review, $admin, $reason) {
                // Call model's reject method which handles rating recalculation
                $review->reject($admin);
                $this->logReject($review, $reason);

                Log::info('Review rejected by admin', [
                    'review_id' => $review->id,
                    'admin_id' => $admin->id,
                    'client_id' => $review->client_id,
                    'provider_id' => $review->service_provider_id,
                    'reason' => $reason,
                ]);

                ErrorHelper::flashNotification(
                    'Review rejected successfully',
                    'success'
                );

                return redirect()->route('admin.reviews');
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Mark a review as featured (highlights on provider profile).
     */
    public function feature(Review $review)
    {
        try {
            if (!$review->is_active) {
                throw new \Exception('Only active (approved) reviews can be featured');
            }

            $review->update(['is_featured' => true]);

            Log::info('Review featured by admin', [
                'review_id' => $review->id,
                'provider_id' => $review->service_provider_id,
            ]);

            ErrorHelper::flashNotification(
                __('admin.review_featured_successfully'),
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
     * Remove featured status from a review.
     */
    public function unfeature(Review $review)
    {
        try {
            $review->update(['is_featured' => false]);

            Log::info('Review unfeatured by admin', [
                'review_id' => $review->id,
                'provider_id' => $review->service_provider_id,
            ]);

            ErrorHelper::flashNotification(
                __('admin.review_unfeatured_successfully'),
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
     * Delete a review (admin removal).
     */
    public function delete(Review $review)
    {
        try {
            $reviewId = $review->id;
            $providerId = $review->service_provider_id;
            $wasPublished = (bool) $review->is_active;

            DB::transaction(function () use ($review, $providerId, $wasPublished) {
                $this->logAction('delete', $review, ['deleted' => $review->toArray()]);

                $review->delete();

                // Review has no soft deletes and delete() bypasses the model's
                // approve/reject hooks, so the provider's average rating has to
                // be recomputed here or it keeps counting a review that is gone.
                if ($wasPublished && $providerId) {
                    Review::recalculateProviderRating($providerId);
                }
            });

            Log::info('Review deleted by admin', [
                'review_id' => $reviewId,
                'provider_id' => $providerId,
                'admin_id' => Auth::id(),
            ]);

            ErrorHelper::flashNotification(
                __('admin.review_deleted_successfully'),
                'success'
            );

            return redirect()->route('admin.reviews');
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }
}
