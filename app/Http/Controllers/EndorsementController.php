<?php

namespace App\Http\Controllers;

use App\Models\Endorsement;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * EndorsementController - Professional "Recommend" feature
 * 
 * Following SPEEDA V5.0 architecture:
 * - Only clients can endorse providers
 * - Toggle behavior: click to endorse, click again to un-endorse
 * - Returns JSON for Alpine.js instant UI update
 */
class EndorsementController extends Controller
{
    /**
     * Toggle endorsement for a service provider.
     * POST /service-providers/{serviceProvider}/endorse
     * 
     * @param ServiceProvider $serviceProvider
     * @return RedirectResponse
     */
    public function toggle(Request $request, ServiceProvider $serviceProvider)
    {
        $user = auth()->user();

        // Check authentication
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to recommend providers.');
        }

        // Only clients can endorse
        if (!$user->isClient()) {
            return redirect()->back()->with('error', 'Only clients can recommend providers.');
        }

        // Cannot endorse yourself
        if ($serviceProvider->user_id === $user->id) {
            return redirect()->back()->with('error', 'You cannot recommend your own profile.');
        }

        try {
            $message = DB::transaction(function () use ($serviceProvider, $user) {
                // Serialize toggles for this provider and keep the stored count in sync.
                $provider = ServiceProvider::whereKey($serviceProvider->id)->lockForUpdate()->firstOrFail();
                $existing = $provider->endorsements()->where('user_id', $user->id)->first();
                if ($existing) {
                    $existing->delete();
                } else {
                    $provider->endorsements()->create(['user_id' => $user->id]);
                }
                $provider->update(['endorsement_count' => $provider->endorsements()->count()]);

                return $existing ? 'Recommendation removed.' : 'Provider recommended!';
            });

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Endorsement toggle failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'provider_id' => $serviceProvider->id,
            ]);

            return redirect()->back()->with('error', 'An error occurred. Please try again.');
        }
    }
}
