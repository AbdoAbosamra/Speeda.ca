<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Translation Service - FIXED VERSION
 *
 * CRITICAL FIX: Removed dangerous dictionary fallback that breaks words.
 * Now uses only Google Translate API or returns null (requires manual translation).
 *
 * This prevents word-breaking bugs like:
 * - "Professional" → "Professفيonal" (when searching for "in")
 * - "Microservices" → "Microخدمات" (when searching for "services")
 */
class TranslationService
{
    /**
     * Translate text from English to target language
     *
     * @param  string  $text  Text to translate
     * @param  string  $targetLanguage  Target language code (ar, fr)
     * @return string|null Translated text or null on failure
     */
    public function translate(string $text, string $targetLanguage): ?string
    {
        if (empty(trim($text))) {
            return null;
        }

        // Use Google Translate API if configured
        if ($this->isGoogleTranslateConfigured()) {
            return $this->translateWithGoogle($text, $targetLanguage);
        }

        // ❌ REMOVED: Dangerous dictionary fallback that breaks words
        // Dictionary approach is fundamentally flawed because:
        // 1. Partial string matching breaks words (e.g., "in" inside "Professional")
        // 2. Cannot handle context properly
        // 3. Unmaintainable for large vocabulary
        //
        // SOLUTION: Return null and require manual translation in admin panel
        // OR: Admin can use Google Translate API if configured

        Log::info('Translation requested but Google Translate API not configured', [
            'text' => $text,
            'target' => $targetLanguage,
        ]);

        return null; // Require manual translation
    }

    /**
     * Check if Google Translate API is configured
     */
    protected function isGoogleTranslateConfigured(): bool
    {
        return ! empty(config('services.google_translate.api_key'));
    }

    /**
     * Translate using Google Translate API
     */
    protected function translateWithGoogle(string $text, string $targetLanguage): ?string
    {
        try {
            $apiKey = config('services.google_translate.api_key');
            $url = 'https://translation.googleapis.com/language/translate/v2';

            $response = Http::timeout(10)->post($url, [
                'key' => $apiKey,
                'q' => $text,
                'source' => 'en',
                'target' => $targetLanguage,
                'format' => 'text',
            ]);

            if ($response->successful() && isset($response->json()['data']['translations'][0]['translatedText'])) {
                return $response->json()['data']['translations'][0]['translatedText'];
            }

            Log::warning('Google Translate API error', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Translation service error', [
                'message' => $e->getMessage(),
                'text' => $text,
                'target' => $targetLanguage,
            ]);

            return null;
        }
    }

    /**
     * Batch translate multiple texts (for admin auto-translate feature)
     *
     * @param  array  $texts  Array of texts to translate
     * @param  string  $targetLanguage  Target language code
     * @return array Array of translated texts (same order as input)
     */
    public function batchTranslate(array $texts, string $targetLanguage): array
    {
        if (! $this->isGoogleTranslateConfigured()) {
            return array_fill(0, count($texts), null);
        }

        $results = [];
        foreach ($texts as $text) {
            $results[] = $this->translate($text, $targetLanguage);
        }

        return $results;
    }
}
