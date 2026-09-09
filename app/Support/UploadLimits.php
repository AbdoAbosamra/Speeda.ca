<?php

namespace App\Support;

/**
 * What PHP will actually accept for an upload, in one place.
 *
 * The forms validate against their own limits (5 MB for a photo, 10 MB for a
 * gallery image), but PHP rejects an oversized file long before validation runs
 * — the file never reaches the application, and all Laravel can say is that it
 * "failed to upload". So the number the UI promises has to be the smaller of
 * the two, and the deploy check has to know what the forms need.
 */
final class UploadLimits
{
    /** Largest single file any form accepts, in megabytes. */
    public const MAX_FILE_MB = 10;

    /**
     * Worst case for one submit of the admin provider form: four gallery
     * images plus the photo, the consent proof and the certification.
     */
    public const MAX_REQUEST_MB = 4 * self::MAX_FILE_MB + 5 + 5 + 5;

    /**
     * PHP's per-file ceiling in megabytes.
     */
    public static function phpFileMb(): float
    {
        return self::toBytes(ini_get('upload_max_filesize')) / 1048576;
    }

    /**
     * PHP's whole-request ceiling in megabytes.
     */
    public static function phpRequestMb(): float
    {
        return self::toBytes(ini_get('post_max_size')) / 1048576;
    }

    /**
     * The limit to actually show and enforce for a field: never advertise more
     * than PHP will take, or people hit a failure the form told them was fine.
     */
    public static function effectiveMb(float $formLimitMb): float
    {
        $php = self::phpFileMb();

        return $php > 0 ? min($formLimitMb, $php) : $formLimitMb;
    }

    /**
     * PHP shorthand size ("20M", "512K") to bytes. An empty or unlimited
     * setting returns 0, which callers read as "no ceiling to apply".
     */
    public static function toBytes(string|false|null $value): int
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '-1') {
            return 0;
        }

        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
