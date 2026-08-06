<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\ServiceProvider;

/**
 * Review Observer
 * 
 * Automatically recalculates provider rating when reviews change.
 * This ensures the calculated_rating column stays in sync.
 */
class ReviewObserver
{
    /**
     * Handle the Review "created" event.
     * Recalculate provider rating when a new review is created.
     */
    public function created(Review $review): void
    {
        $this->recalculateProviderRating($review);
    }

    /**
     * Handle the Review "updated" event.
     * Recalculate provider rating when a review is updated (e.g., status change, rating change).
     */
    public function updated(Review $review): void
    {
        // Only recalculate if relevant fields changed
        if ($review->wasChanged(['is_active', 'rating'])) {
            $this->recalculateProviderRating($review);
        }
    }

    /**
     * Handle the Review "deleted" event.
     * Recalculate provider rating when a review is deleted.
     */
    public function deleted(Review $review): void
    {
        $this->recalculateProviderRating($review);
    }

    /**
     * Handle the Review "restored" event.
     * Recalculate provider rating when a soft-deleted review is restored.
     */
    public function restored(Review $review): void
    {
        $this->recalculateProviderRating($review);
    }

    /**
     * Recalculate the provider's rating.
     * 
     * @param Review $review
     * @return void
     */
    private function recalculateProviderRating(Review $review): void
    {
        $provider = ServiceProvider::find($review->service_provider_id);

        if ($provider) {
            $provider->recalculateRating();
        }
    }
}
