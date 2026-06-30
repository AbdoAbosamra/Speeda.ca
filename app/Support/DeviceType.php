<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Privacy-safe device categoriser.
 *
 * We deliberately DO NOT store the full User-Agent string (it can be a
 * fingerprinting vector). Instead we reduce it to a coarse, non-identifying
 * bucket: mobile / tablet / desktop / bot / unknown. This is enough for
 * "clicks by device type" reporting without tracking individuals.
 */
class DeviceType
{
    public const MOBILE = 'mobile';
    public const TABLET = 'tablet';
    public const DESKTOP = 'desktop';
    public const BOT = 'bot';
    public const UNKNOWN = 'unknown';

    /**
     * Reduce a raw User-Agent to a coarse device bucket.
     */
    public static function fromUserAgent(?string $userAgent): string
    {
        $ua = Str::lower(trim((string) $userAgent));

        if ($ua === '') {
            return self::UNKNOWN;
        }

        if (Str::contains($ua, ['bot', 'crawler', 'spider', 'crawling', 'facebookexternalhit', 'slurp', 'bingpreview'])) {
            return self::BOT;
        }

        if (Str::contains($ua, ['ipad', 'tablet', 'kindle', 'silk', 'playbook'])) {
            return self::TABLET;
        }

        if (Str::contains($ua, ['mobi', 'iphone', 'ipod', 'android', 'blackberry', 'windows phone', 'webos'])) {
            // Android tablets omit "mobile"; phones include "mobi".
            if (Str::contains($ua, 'android') && ! Str::contains($ua, 'mobi')) {
                return self::TABLET;
            }

            return self::MOBILE;
        }

        return self::DESKTOP;
    }
}
