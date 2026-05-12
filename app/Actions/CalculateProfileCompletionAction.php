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
        $addressComplete = filled($serviceProvider->address);
        
        $servicesComplete = is_array($serviceProvider->services_offered)
            ? count(array_filter($serviceProvider->services_offered)) > 0
            : filled($serviceProvider->services_offered);

        // Updated scoring (Unified Logic):
        // - Profile photo: 40%
        // - Years of experience: 20%
        // - Address: 20%
        // - Services offered: 20%
        $percent = 0;

        if ($profilePhotoComplete) $percent += 40;
        if ($experienceYearsComplete) $percent += 20;
        if ($addressComplete) $percent += 20;
        if ($servicesComplete) $percent += 20;

        $percent = min(100, max(0, $percent));

        // Quiet save to avoid infinite observer loops.
        $serviceProvider->updateQuietly([
            'profile_completion_percent' => (int) $percent,
        ]);

        return (int) $percent;
    }
}

