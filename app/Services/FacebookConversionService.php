<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FacebookConversionService
 *
 * Sends server-side events to the Meta Conversion API (CAPI)
 * for deduplication with client-side Pixel events.
 *
 * All calls are wrapped in try/catch — failures are logged
 * but NEVER break the request flow.
 */
class FacebookConversionService
{
    protected string $pixelId;
    protected string $accessToken;
    protected string $apiVersion;
    protected bool $enabled;

    public function __construct()
    {
        $this->pixelId = config('facebook.pixel_id', '');
        $this->accessToken = config('facebook.access_token', '');
        $this->apiVersion = config('facebook.graph_api_version', 'v21.0');
        $this->enabled = config('facebook.capi_enabled', false);
    }

    /**
     * Send an event to the Conversion API.
     *
     * @param string $eventName   e.g. 'ViewContent', 'Lead', 'CompleteRegistration'
     * @param string $eventId     Unique event ID for deduplication with client-side pixel
     * @param array  $customData  Custom parameters (content_name, content_ids, etc.)
     * @param array  $userData    User data (email, phone — will be SHA-256 hashed)
     * @param string|null $sourceUrl  The URL where the event occurred
     * @return bool
     */
    public function sendEvent(
        string $eventName,
        string $eventId,
        array $customData = [],
        array $userData = [],
        ?string $sourceUrl = null
    ): bool {
        if (!$this->enabled) {
            return false;
        }

        try {
            $eventData = [
                'event_name' => $eventName,
                'event_time' => time(),
                'event_id' => $eventId,
                'event_source_url' => $sourceUrl ?? request()->fullUrl(),
                'action_source' => 'website',
                'user_data' => $this->buildUserData($userData),
            ];

            if (!empty($customData)) {
                $eventData['custom_data'] = $customData;
            }

            $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->pixelId}/events";

            $response = Http::post($url, [
                'data' => [json_encode([$eventData])],
                'access_token' => $this->accessToken,
            ]);

            if ($response->failed()) {
                Log::warning('Facebook CAPI event failed', [
                    'event' => $eventName,
                    'event_id' => $eventId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            Log::debug('Facebook CAPI event sent', [
                'event' => $eventName,
                'event_id' => $eventId,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Facebook CAPI exception', [
                'event' => $eventName,
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Build hashed user data for the Conversion API.
     * All PII (email, phone) is SHA-256 hashed before sending.
     */
    protected function buildUserData(array $userData): array
    {
        $hashedData = [];

        // Client IP address
        $hashedData['client_ip_address'] = request()->ip();

        // Client user agent
        $hashedData['client_user_agent'] = request()->userAgent();

        // Facebook browser ID (fbp cookie)
        if (request()->cookie('_fbp')) {
            $hashedData['fbp'] = request()->cookie('_fbp');
        }

        // Facebook click ID (fbc cookie)
        if (request()->cookie('_fbc')) {
            $hashedData['fbc'] = request()->cookie('_fbc');
        }

        // Hash email if provided
        if (!empty($userData['email'])) {
            $hashedData['em'] = [hash('sha256', strtolower(trim($userData['email'])))];
        }

        // Hash phone if provided
        if (!empty($userData['phone'])) {
            // Normalize: remove non-digit characters except +
            $phone = preg_replace('/[^0-9]/', '', $userData['phone']);
            $hashedData['ph'] = [hash('sha256', $phone)];
        }

        // Hash external ID if provided (e.g., user ID)
        if (!empty($userData['external_id'])) {
            $hashedData['external_id'] = [hash('sha256', (string) $userData['external_id'])];
        }

        return $hashedData;
    }

    /**
     * Convenience: Send ViewContent event.
     */
    public function trackViewContent(string $eventId, array $contentData, array $userData = []): bool
    {
        return $this->sendEvent('ViewContent', $eventId, $contentData, $userData);
    }

    /**
     * Convenience: Send Lead event.
     */
    public function trackLead(string $eventId, array $leadData, array $userData = []): bool
    {
        return $this->sendEvent('Lead', $eventId, $leadData, $userData);
    }

    /**
     * Convenience: Send CompleteRegistration event.
     */
    public function trackCompleteRegistration(string $eventId, array $registrationData, array $userData = []): bool
    {
        return $this->sendEvent('CompleteRegistration', $eventId, $registrationData, $userData);
    }
}
