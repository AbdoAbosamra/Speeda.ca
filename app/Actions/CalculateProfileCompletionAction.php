<?php

namespace App\Actions;

use App\Models\ServiceProvider;

class CalculateProfileCompletionAction
{
    public function execute(ServiceProvider $serviceProvider): int
    {
        $profilePhotoComplete = filled($serviceProvider->profile_image);
        $experienceYearsComplete = filled($serviceProvider->experience_years) && (int) $serviceProvider->experience_years > 0;
        $addressComplete = filled($serviceProvider->address);

        $bioComplete = filled($serviceProvider->bio);
        $galleryCount = $serviceProvider->getMedia('provider_gallery')->count();
        $galleryComplete = $galleryCount >= 4;

        $servicesComplete = is_array($serviceProvider->services_offered)
            ? count(array_filter($serviceProvider->services_offered)) > 0
            : filled($serviceProvider->services_offered);

        // Weighted scoring:
        // - Profile photo: 30%
        // - Years of experience: 30%
        // - Address: 20%
        // - Remaining fields (20%): bio 10% + gallery (>=4) 5% + services offered 5%
        $percent = 0;

        if ($profilePhotoComplete) {
            $percent += 30;
        }

        if ($experienceYearsComplete) {
            $percent += 30;
        }

        if ($addressComplete) {
            $percent += 20;
        }

        if ($bioComplete) {
            $percent += 10;
        }

        if ($galleryComplete) {
            $percent += 5;
        }

        if ($servicesComplete) {
            $percent += 5;
        }

        $percent = min(100, max(0, $percent));

        // Quiet save to avoid infinite observer loops.
        $serviceProvider->updateQuietly([
            'profile_completion_percent' => (int) $percent,
        ]);

        return (int) $percent;
    }
}

