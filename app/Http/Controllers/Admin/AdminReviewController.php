<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use App\Helpers\ErrorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminReviewController extends Controller
{
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
            if ($request->get('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->get('status') === 'pending') {
                $query->where('is_active', false)->whereNull('admin_approved_at');
            }

            // Filter by rating
            if ($request->has('rating')) {
                $query->where('rating', $request->get('rating'));
            }

            // Filter by provider
            if ($request->has('provider_id')) {
                $query->where('service_provider_id', $request->get('provider_id'));
            }

            $perPage = $request->get('per_page', 20);
            $allowedPerPage = [10, 25, 50, 100];
            if (!in_array($perPage, $allowedPerPage)) {
                $perPage = 20;
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

            return view('admin.reviews.index', compact('reviews', 'stats'));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->route('admin.dashboard');
        }
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
            $review->delete();

            Log::info('Review deleted by admin', [
                'review_id' => $reviewId,
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
