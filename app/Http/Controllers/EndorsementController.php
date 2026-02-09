<?php

namespace App\Http\Controllers;

use App\Models\Endorsement;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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
     * @return JsonResponse
     */
    public function toggle(ServiceProvider $serviceProvider): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        // Check if user is authenticated
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => __('endorsements.login_required'),
            ], 401);
        }

        // Only clients can endorse
        if (!$user->isClient()) {
            return response()->json([
                'success' => false,
                'message' => __('endorsements.clients_only'),
            ], 403);
        }

        // Cannot endorse yourself
        if ($serviceProvider->user_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('endorsements.cannot_endorse_self'),
            ], 403);
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

                return response()->json([
                    'success' => true,
                    'endorsed' => $endorsed,
                    'count' => $serviceProvider->endorsement_count,
                    'message' => __('endorsements.toggle_success'),
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Endorsement toggle failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'service_provider_id' => $serviceProvider->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => __('general.error_occurred'),
            ], 500);
        }
    }
}
