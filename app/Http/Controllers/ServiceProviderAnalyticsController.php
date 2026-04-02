<?php

namespace App\Http\Controllers;

use App\Actions\TrackProviderClickAction;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;

class ServiceProviderAnalyticsController extends Controller
{
    public function trackClick(Request $request, ServiceProvider $serviceProvider)
    {
        $data = $request->validate([
            'action_type' => ['required', 'in:click_whatsapp'],
        ]);

        // PRIVACY: No IP address passed — action uses session fingerprint internally.
        app(TrackProviderClickAction::class)->execute(
            $serviceProvider->id,
            $data['action_type']
        );

        // Must be fast: frontend redirects immediately after this request.
        return response()->json(['success' => true]);
    }
}
