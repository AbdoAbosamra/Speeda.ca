<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RatingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store or update a rating for a service provider.
     * Each user can only have one rating per provider.
     */
    public function store(Request $request, ServiceProvider $serviceProvider)
    {
        try {
            $validated = $request->validate([
                'rating' => 'required|integer|min:1|max:5',
            ]);

            /** @var User $user */
            $user = Auth::user();

            // STRICT: Only clients can rate
            if (!$user->isClient()) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => __('ratings.only_clients_can_rate')], 403);
                }
                abort(403, __('ratings.only_clients_can_rate'));
            }

            // Prevent self-rating
            if ($serviceProvider->user_id === $user->id) {
                throw ValidationException::withMessages([
                    'rating' => [__('ratings.cannot_rate_self')],
                ]);
            }

            // Create or update the rating (upsert)
            $rating = Rating::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'service_provider_id' => $serviceProvider->id,
                ],
                [
                    'rating' => $validated['rating'],
                ]
            );

            Log::info('Rating submitted', [
                'user_id' => $user->id,
                'provider_id' => $serviceProvider->id,
                'rating' => $validated['rating'],
            ]);

            // Return JSON for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('ratings.submitted_successfully'),
                    'rating' => $rating->rating,
                    'average' => $serviceProvider->fresh()->rating,
                ]);
            }

            return redirect()->back()->with('success', __('ratings.submitted_successfully'));
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Rating submission failed', [
                'error' => $e->getMessage(),
                'provider_id' => $serviceProvider->id,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('ratings.submission_failed'),
                ], 500);
            }

            return redirect()->back()->with('error', __('ratings.submission_failed'));
        }
    }

    /**
     * Get the current user's rating for a provider.
     */
    public function getUserRating(ServiceProvider $serviceProvider)
    {
        if (!Auth::check()) {
            return response()->json(['rating' => null]);
        }

        $rating = Rating::where('user_id', Auth::id())
            ->where('service_provider_id', $serviceProvider->id)
            ->first();

        return response()->json([
            'rating' => $rating?->rating,
            'average' => $serviceProvider->rating,
        ]);
    }
}
