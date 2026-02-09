<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ServiceProvider;
use App\Models\Booking;
use App\Models\User;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Helpers\ErrorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Display reviews for a service provider (public - only active reviews).
     */
    public function index(ServiceProvider $provider)
    {
        // Only show active (approved) reviews to public
        $reviews = $provider->activeReviews()
            ->with(['client', 'approvedBy'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('reviews.index', compact('provider', 'reviews'));
    }

    /**
     * Show review submission form for a service provider.
     */
    public function create(ServiceProvider $provider)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user || !$user->isClient()) {
            abort(403, 'Only clients can write reviews');
        }

        // Check if user already reviewed this provider
        $existing = Review::where('service_provider_id', $provider->id)
            ->where('client_id', $user->id)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', __('reviews.review_already_exists'));
        }

        return view('reviews.create', compact('provider'));
    }

    /**
     * Display the specified review (public - only if active).
     */
    public function show(Review $review)
    {
        // Only show active reviews to public
        if (!$review->is_active) {
            abort(404, 'Review not found');
        }

        $review->load(['client', 'serviceProviderProfile.user', 'approvedBy']);

        return view('reviews.show', compact('review'));
    }

    /**
     * Store a new review - STRICT: Only CLIENT users can create reviews.
     */
    public function store(StoreReviewRequest $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // STRICT: Only clients can create reviews
            if (!$user || !$user->isClient()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only clients can create reviews'
                    ], 403);
                }
                abort(403, 'Only clients can create reviews');
            }

            $validated = $request->validated();

            return DB::transaction(function () use ($user, $validated, $request) {
                $provider = ServiceProvider::findOrFail($validated['service_provider_id']);

                // STRICT: Client cannot review themselves
                if ($provider->user_id === $user->id) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => __('reviews.review_self_not_allowed')
                        ], 422);
                    }
                    return redirect()->back()->with('error', __('reviews.review_self_not_allowed'));
                }

                // STRICT: One review per client per provider
                $existingReview = Review::where('service_provider_id', $provider->id)
                    ->where('client_id', $user->id)
                    ->first();

                if ($existingReview) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => __('reviews.review_already_exists')
                        ], 422);
                    }
                    return redirect()->back()->with('error', __('reviews.review_already_exists'));
                }

                // Optional: Check if client has booking with provider for verification
                $booking = null;
                if (isset($validated['booking_id'])) {
                    $booking = Booking::find($validated['booking_id']);
                    if (!$booking || $booking->client_id !== $user->id || $booking->service_provider_id !== $provider->id) {
                        throw new \Exception('Invalid booking association');
                    }
                }

                // Create the review (starts as inactive - requires admin approval)
                $review = Review::create([
                    'service_provider_id' => $provider->id,
                    'client_id' => $user->id,
                    'booking_id' => $booking?->id,
                    'rating' => $validated['rating'],
                    'review_text' => $validated['review_text'] ?? null,
                    'rating_breakdown' => $validated['rating_breakdown'] ?? null,
                    'is_verified' => !empty($booking), // Auto-verify if linked to booking
                    'is_featured' => false,
                    'is_active' => false, // Must be approved by admin before showing on website
                ]);

                Log::info('Review created by client', [
                    'review_id' => $review->id,
                    'client_id' => $user->id,
                    'provider_id' => $provider->id,
                    'rating' => $review->rating,
                    'is_verified' => $review->is_verified,
                ]);

                if ($request->expectsJson()) {
                    // Refresh provider data to get updated rating and count
                    $provider->refresh();

                    return response()->json([
                        'success' => true,
                        'message' => __('reviews.review_submitted_pending_approval'),
                        'review' => [
                            'id' => $review->id,
                            'rating' => $provider->rating,
                            'reviews_count' => $provider->reviews_count,
                        ]
                    ]);
                }

                ErrorHelper::flashNotification(
                    __('reviews.review_submitted_pending_approval'),
                    'success'
                );

                return redirect()->route('service-providers.show', $provider)
                    ->with('success', __('reviews.review_submitted_pending_approval'));
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            Log::warning('Review creation failed', ['error' => $e->getMessage()]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $error['message']
                ], 422);
            }

            return redirect()->back()->with('error', $error['message'])->withInput();
        }
    }

    /**
     * Edit form for own review (before admin approval).
     */
    public function edit(Review $review)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($review->client_id !== $user->id) {
            abort(403, 'You can only edit your own reviews');
        }

        if ($review->is_active) {
            abort(403, 'You cannot edit a review that has been approved by our team');
        }

        return view('reviews.edit', compact('review'));
    }

    /**
     * Update a review - STRICT: Only the ORIGINAL AUTHOR (client) can update their own review before admin approval.
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // STRICT: Only the original author (client) can edit their review
            if ($review->client_id !== $user->id) {
                abort(403, 'You can only edit your own reviews');
            }

            // STRICT: Cannot edit reviews that are already active (approved by admin)
            if ($review->is_active) {
                return redirect()->back()->with('error', __('reviews.review_cannot_edit_approved'));
            }

            $validated = $request->validated();

            return DB::transaction(function () use ($review, $user, $validated) {
                $review->update([
                    'rating' => $validated['rating'] ?? $review->rating,
                    'review_text' => $validated['review_text'] ?? $review->review_text,
                    'rating_breakdown' => $validated['rating_breakdown'] ?? $review->rating_breakdown,
                ]);

                Log::info('Review updated by client', [
                    'review_id' => $review->id,
                    'client_id' => $user->id,
                    'rating' => $review->rating,
                ]);

                ErrorHelper::flashNotification(
                    __('reviews.review_updated_pending_reapproval'),
                    'success'
                );

                return redirect()->route('service-providers.show', $review->serviceProviderProfile)
                    ->with('success', __('reviews.review_updated_pending_reapproval'));
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            return redirect()->back()->with('error', $error['message'])->withInput();
        }
    }

    /**
     * Delete a review - STRICT: Only author OR admin can delete.
     */
    public function destroy(Review $review)
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // STRICT: Only the author (client) or admin can delete
            if ($review->client_id !== $user->id && !$user->isAdmin()) {
                abort(403, 'You are not authorized to delete this review');
            }

            // Admin cannot delete approved reviews without using reject
            if ($user->isAdmin() && $review->is_active) {
                return redirect()->back()->with('error', 'To remove an approved review, use the reject functionality');
            }

            $reviewId = $review->id;
            $provider = $review->serviceProviderProfile;
            $review->delete();

            Log::info('Review deleted', [
                'review_id' => $reviewId,
                'deleted_by_user_id' => $user->id,
                'deleted_by_role' => $user->role,
            ]);

            ErrorHelper::flashNotification(
                __('reviews.review_deleted_successfully'),
                'success'
            );

            return redirect()->route('service-providers.show', $provider)
                ->with('success', __('reviews.review_deleted_successfully'));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            return redirect()->back()->with('error', $error['message']);
        }
    }
}
