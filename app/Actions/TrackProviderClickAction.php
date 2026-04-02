<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrackProviderClickAction
{
    /**
     * Track a provider click event (e.g. WhatsApp button).
     *
     * PRIVACY: No IP address is stored. A hashed session fingerprint
     * is recorded for analytics purposes only.
     */
    public function execute(int $providerId, string $actionType): bool
    {
        $sessionHash = $this->generateSessionHash();
        $now = now();

        try {
            DB::table('analytics')->insert([
                'provider_id' => $providerId,
                'action_type' => $actionType,
                'session_hash' => $sessionHash,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

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

    /**
     * Generate a privacy-safe session fingerprint hash.
     */
    private function generateSessionHash(): string
    {
        $sessionId = session()->getId();
        $userAgent = request()->userAgent() ?? 'unknown';

        if (empty($sessionId)) {
            return hash('sha256', 'anonymous|' . $userAgent);
        }

        return hash('sha256', $sessionId . '|' . $userAgent);
    }
}
