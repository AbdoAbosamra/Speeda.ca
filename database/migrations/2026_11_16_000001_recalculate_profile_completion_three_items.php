<?php

use App\Actions\CalculateProfileCompletionAction;
use App\Models\ServiceProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

/**
 * Profile completion is now photo + experience + description (a third each).
 * Recompute every stored percent so the dashboards and filters agree at once.
 */
return new class extends Migration
{
    public function up(): void
    {
        $action = app(CalculateProfileCompletionAction::class);

        ServiceProvider::query()
            ->select(['id', 'profile_image', 'bio', 'experience_years', 'profile_completion_percent'])
            ->chunkById(200, function ($providers) use ($action) {
                foreach ($providers as $provider) {
                    $action->execute($provider);
                }
            });

        foreach (['admin_dash_profile_health', 'admin_dash_action_center', 'home_featured_providers'] as $key) {
            Cache::forget($key);
        }
    }

    public function down(): void
    {
        // Data-only: the old percentages cannot be restored, and need not be.
    }
};
