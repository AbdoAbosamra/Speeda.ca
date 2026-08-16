<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Actions\CalculateProfileCompletionAction;
use App\Models\ServiceProvider;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Backfill profile completion % for existing providers after deployment.
// This is intentionally a one-time command (no page-load computation).
Artisan::command('providers:backfill-profile-completion', function () {
    $this->info('Starting profile completion backfill...');

    $action = app(CalculateProfileCompletionAction::class);
    $count = 0;

    ServiceProvider::query()
        ->select(['id', 'profile_image', 'bio', 'experience_years', 'address', 'services_offered'])
        ->chunkById(100, function ($providers) use ($action, &$count) {
        foreach ($providers as $provider) {
            $action->execute($provider);
            $count++;
        }
    });

    $this->info("Backfill completed. Updated {$count} providers.");
})->purpose('Recalculate profile_completion_percent for all existing providers');

use Illuminate\Support\Facades\Schedule;

Schedule::command('providers:send-journey-emails')
    ->dailyAt('10:00')
    ->withoutOverlapping()
    ->runInBackground();

// Regenerate the XML sitemap daily so new providers, categories,
// locations, and blog posts are picked up automatically. Runs at
// 03:00 to avoid peak traffic and after any nightly data jobs.
Schedule::command('seo:generate-sitemap')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground();

