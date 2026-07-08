<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates Canadian phone numbers in various formats.
 * Accepts: (514) 555-0123, 514-555-0123, 514.555.0123, 5145550123
 * Must be 10 digits, cannot start with 0 or 1
 */
class CanadianPhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Empty values should be handled by nullable/required rules
        if (empty($value) || trim($value) === '') {
            return;
        }

        // Owner whitelist: allow the site owner's non-Canadian (e.g. Egyptian)
        // number(s) to pass. This is the ONLY way a foreign number is accepted;
        // every other non-Canadian number is still rejected below.
        $inputDigits = preg_replace('/\D/', '', $value);
        if ($inputDigits !== '' && in_array($inputDigits, (array) config('owner.exempt_phones', []), true)) {
            return;
        }

        // Handle extensions by removing them first
        $cleanValue = preg_replace('/\b(ext|extension|x)\s*\d+$/i', '', $value);

        // Remove all non-digit characters
        $phoneDigits = preg_replace('/\D/', '', $cleanValue);

        // If starts with 1 (country code), remove it
        if (strlen($phoneDigits) === 11 && $phoneDigits[0] === '1') {
            $phoneDigits = substr($phoneDigits, 1);
        }

        // Must be exactly 10 digits after normalization
        if (strlen($phoneDigits) !== 10) {
            $fail(__('validation.canadian_phone_length'));
            return;
        }

        // First digit cannot be 0 or 1 (Canadian format)
        if (in_array($phoneDigits[0], ['0', '1'])) {
            $fail(__('validation.canadian_phone_invalid_start'));
            return;
        }

        // Basic format validation - much more lenient for better user experience
        // Just ensure it looks like a reasonable North American phone number
        // Allow all combinations except obviously invalid patterns
    }
}
