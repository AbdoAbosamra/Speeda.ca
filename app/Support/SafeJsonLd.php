<?php

namespace App\Support;

/**
 * Makes JSON-LD safe to print inside a <script> tag.
 *
 * The SEO package encodes JSON-LD with JSON_UNESCAPED_SLASHES, so a page
 * title containing "</script>" (a search for it, say) closed the tag early
 * and whatever followed ran as a real script. Inside a JSON string "<\/" and
 * "<" mean exactly the same characters, so escaping changes nothing a
 * search engine reads.
 */
final class SafeJsonLd
{
    /**
     * Escape the body of every JSON-LD block in a chunk of head HTML.
     */
    public static function harden(string $html): string
    {
        return preg_replace_callback(
            '#(<script type="application/ld\+json">)(.*?)(</script>)#s',
            fn (array $m) => $m[1] . self::escape($m[2]) . $m[3],
            $html
        ) ?? $html;
    }

    /**
     * JSON for a <script type="application/ld+json"> tag.
     */
    public static function encode(mixed $data): string
    {
        return self::escape(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private static function escape(string $json): string
    {
        return str_replace(['</', '<!--'], ['<\/', '<!--'], $json);
    }
}
