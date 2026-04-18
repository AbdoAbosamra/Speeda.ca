<?php

namespace App\Actions;

use App\Models\ServiceProvider;

class CalculateProfileCompletionAction
{
    public function execute(ServiceProvider $serviceProvider): int
    {
        // Identify completion status for core fields
        $profilePhotoComplete = filled($serviceProvider->profile_image);
        $experienceYearsComplete = filled($serviceProvider->experience_years) && (int) $serviceProvider->experience_years > 0;
        $bioComplete = filled($serviceProvider->bio);
        $servicesComplete = is_array($serviceProvider->services_offered)
            ? count(array_filter($serviceProvider->services_offered)) > 0
            : filled($serviceProvider->services_offered);

        // Updated scoring (simplified to 4 core points):
        // - Profile photo: 40%
        // - Years of experience: 40%
        // - Bio (Description): 10%
        // - Services offered: 10%
        $percent = 0;

        if ($profilePhotoComplete) {
            $percent += 40;
        }

        if ($experienceYearsComplete) {
            $percent += 40;
        }

        if ($bioComplete) {
            $percent += 10;
        }

        if ($servicesComplete) {
            $percent += 10;
        }

        $percent = min(100, max(0, $percent));

        // Quiet save to avoid infinite observer loops.
        $serviceProvider->updateQuietly([
            'profile_completion_percent' => (int) $percent,
        ]);

        return (int) $percent;
    }
}

