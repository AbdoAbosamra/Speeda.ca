<?php

namespace App\Support;

/**
 * One place that turns a stored phone number into a working WhatsApp link.
 *
 * Numbers reach the database in several shapes: "+16135550123",
 * "613-555-0123", "(613) 555 0123", "0016135550123", the owner's Egyptian
 * "01289121218". WhatsApp only accepts the full international number as
 * digits, so every link on the site is built here — a ten-digit number is a
 * Canadian/US number (+1), never the Egyptian +20 the profile used to guess.
 */
final class WhatsApp
{
    /**
     * International number as digits only (no "+"), or null if unusable.
     */
    public static function digits(?string $raw): ?string
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return null;
        }

        $hasPlus = str_starts_with($raw, '+');
        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === '' || $digits === null) {
            return null;
        }

        if (! $hasPlus) {
            if (str_starts_with($digits, '00')) {
                // 00 is the international prefix: 0016135550123.
                $digits = substr($digits, 2);
            } elseif (strlen($digits) === 10 && $digits[0] !== '0') {
                // The site serves Canada: 6135550123 → 16135550123.
                $digits = '1' . $digits;
            } elseif (strlen($digits) === 11 && str_starts_with($digits, '01')) {
                // Egyptian mobile written locally: 01289121218 → 201289121218.
                $digits = '20' . substr($digits, 1);
            }
        }

        // E.164 allows 8–15 digits; anything else cannot be a WhatsApp account.
        return strlen($digits) >= 8 && strlen($digits) <= 15 ? $digits : null;
    }

    /**
     * Click-to-chat link with an optional pre-filled message.
     */
    public static function url(?string $raw, ?string $message = null): ?string
    {
        $digits = self::digits($raw);

        if ($digits === null) {
            return null;
        }

        $url = 'https://wa.me/' . $digits;

        if (! filled($message)) {
            return $url;
        }

        // Lang files saved with Windows line endings would add a stray \r.
        $message = str_replace(["\r\n", "\r"], "\n", trim($message));

        return $url . '?text=' . rawurlencode($message);
    }

    /**
     * Readable form for display: +1 (613) 864-9118, else +<digits>.
     */
    public static function display(?string $raw): ?string
    {
        $digits = self::digits($raw);

        if ($digits === null) {
            return null;
        }

        if (strlen($digits) === 11 && $digits[0] === '1') {
            return sprintf('+1 (%s) %s-%s', substr($digits, 1, 3), substr($digits, 4, 3), substr($digits, 7));
        }

        return '+' . $digits;
    }

    /**
     * SPEEDA's own WhatsApp, for providers who want to publish an offer.
     */
    public static function speedaNumber(): string
    {
        return (string) config('owner.offers_whatsapp', '+16138649118');
    }

    public static function contactSpeedaUrl(): ?string
    {
        return self::url(self::speedaNumber(), __('offers.contact_whatsapp_message'));
    }
}
