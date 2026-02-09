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
    public function toggle(Request $request, ServiceProvider $serviceProvider): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        // Check if user is authenticated
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('endorsements.login_required'),
                ], 401);
            }
            return redirect()->route('login')->with('error', __('endorsements.login_required'));
        }

        // Only clients can endorse
        if (!$user->isClient()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('endorsements.clients_only'),
                ], 403);
            }
            abort(403, __('endorsements.clients_only'));
        }

        // Cannot endorse yourself
        if ($serviceProvider->user_id === $user->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('endorsements.cannot_endorse_self'),
                ], 403);
            }
            abort(403, __('endorsements.cannot_endorse_self'));
        }

        try {
            return DB::transaction(function () use ($serviceProvider, $user) {
                // Check if endorsement exists
                $existingEndorsement = Endorsement::where('service_provider_id', $serviceProvider->id)
                    ->where('user_id', $user->id)
                    ->first();

                $endorsed = false;

                if ($existingEndorsement) {
                    // Remove endorsement
                    $existingEndorsement->delete();
                    $serviceProvider->decrement('endorsement_count');
                    $endorsed = false;

                    Log::info('Endorsement removed', [
                        'user_id' => $user->id,
                        'service_provider_id' => $serviceProvider->id,
                    ]);
                } else {
                    // Create endorsement
                    Endorsement::create([
                        'service_provider_id' => $serviceProvider->id,
                        'user_id' => $user->id,
                    ]);
                    $serviceProvider->increment('endorsement_count');
                    $endorsed = true;

                    Log::info('Endorsement added', [
                        'user_id' => $user->id,
                        'service_provider_id' => $serviceProvider->id,
                    ]);
                }

                // Get updated count
                $serviceProvider->refresh();

                $message = $endorsed ? __('endorsements.added') : __('endorsements.removed');

                // ARCHITECTURE COMPLIANCE: Only return JSON for AJAX requests
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'endorsed' => $endorsed,
                        'count' => $serviceProvider->endorsement_count,
                        'message' => $message,
                    ]);
                }

                return redirect()->back()->with('success', $message);
            });
        } catch (\Exception $e) {
            Log::error('Endorsement toggle failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'service_provider_id' => $serviceProvider->id,
            ]);

            // ARCHITECTURE COMPLIANCE: Only return JSON for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('general.error_occurred'),
                ], 500);
            }

            return redirect()->back()->with('error', __('general.error_occurred'));
        }
    }
}
