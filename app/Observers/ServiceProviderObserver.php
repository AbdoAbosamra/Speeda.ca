<?php

namespace App\Observers;

use App\Actions\CalculateProfileCompletionAction;
use App\Models\ServiceProvider;

class ServiceProviderObserver
{
    public function created(ServiceProvider $serviceProvider): void
    {
        app(CalculateProfileCompletionAction::class)->execute($serviceProvider);
    }

    public function updated(ServiceProvider $serviceProvider): void
    {
        app(CalculateProfileCompletionAction::class)->execute($serviceProvider);
    }
}

