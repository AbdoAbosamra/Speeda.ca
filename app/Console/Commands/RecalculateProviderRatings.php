<?php

namespace App\Console\Commands;

use App\Models\ServiceProvider;
use Illuminate\Console\Command;

/**
 * Re-syncs the cached rating columns from the approved reviews.
 *
 * Listing pages read service_providers.calculated_rating instead of averaging
 * reviews per card. ReviewObserver keeps it current; this command repairs it
 * after anything that bypassed the observer (manual SQL, an imported dump).
 */
class RecalculateProviderRatings extends Command
{
    protected $signature = 'providers:recalculate-ratings';

    protected $description = 'Recalculate the cached rating of every service provider from its approved reviews';

    public function handle(): int
    {
        $count = 0;

        ServiceProvider::query()->select('id')->chunkById(200, function ($providers) use (&$count) {
            foreach ($providers as $provider) {
                $provider->recalculateRating();
                $count++;
            }
        });

        $this->info("Recalculated ratings for {$count} providers.");

        return self::SUCCESS;
    }
}
