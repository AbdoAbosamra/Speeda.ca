<?php

namespace App\Actions;

use App\Actions\Concerns\BuildsAnalyticsPayload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrackProviderClickAction
{
    use BuildsAnalyticsPayload;

    /**
     * Track a provider click event (e.g. WhatsApp button).
     *
     * PRIVACY: No IP address and no raw User-Agent are stored. A hashed session
     * fingerprint plus privacy-safe context (category, location, source page,
     * locale, coarse device bucket) is recorded for lead analytics only.
     *
     * @param  array{source_page?:string|null}  $context
     */
    public function execute(int $providerId, string $actionType, array $context = []): bool
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return false;
        }

        $sessionHash = $this->generateSessionHash();
        $now = now();
        $userId = Auth::id();
        $payload = $this->buildAnalyticsPayload($providerId, $actionType, $sessionHash, $now, $userId, $context);

        if (empty($payload)) {
            return false;
        }

        try {
            DB::table('analytics')->insert($payload);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Provider click analytics insert failed', [
                'provider_id' => $providerId,
                'action_type' => $actionType,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
