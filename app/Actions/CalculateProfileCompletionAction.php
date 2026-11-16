<?php

namespace App\Actions;

use App\Models\ServiceProvider;

class CalculateProfileCompletionAction
{
    /**
     * Store the provider's completion percent. The rules live on the model
     * (profile photo, years of experience, description — a third each).
     */
    public function execute(ServiceProvider $serviceProvider): int
    {
        $percent = $serviceProvider->profileCompletionPercent();

        if ((int) $serviceProvider->profile_completion_percent !== $percent) {
            // Quiet save to avoid infinite observer loops.
            $serviceProvider->updateQuietly([
                'profile_completion_percent' => $percent,
            ]);
        }

        return $percent;
    }
}
