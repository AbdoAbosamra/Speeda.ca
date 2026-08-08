<?php

namespace App\Support;

/**
 * Defence-in-depth cleaner for HTML an admin authored in the dashboard editor.
 *
 * The editor (TinyMCE) already refuses to keep <script>, but the browser is not
 * a trust boundary: the same field can be reached with a crafted POST. Because
 * broadcast bodies are rendered unescaped into an email, anything that can
 * execute or phone home is stripped here, on the server, before it is stored.
 *
 * This is a strict allowlist-by-removal cleaner, not a full HTML parser — it
 * removes the categories that matter for email (active content, event
 * handlers, and javascript: URLs) and leaves ordinary formatting alone.
 */
final class AdminHtml
{
    /** Tags removed entirely, including their contents. */
    private const FORBIDDEN_TAGS = [
        'script', 'iframe', 'object', 'embed', 'applet', 'form', 'style', 'link', 'meta', 'base',
    ];

    public static function clean(?string $html): string
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return '';
        }

        // 1. Drop dangerous elements together with whatever they wrap.
        foreach (self::FORBIDDEN_TAGS as $tag) {
            $html = preg_replace('#<' . $tag . '\b[^>]*>.*?</' . $tag . '\s*>#is', '', $html) ?? $html;
            // Unclosed / self-closing variants of the same tags.
            $html = preg_replace('#<' . $tag . '\b[^>]*/?>#is', '', $html) ?? $html;
        }

        // 2. Strip inline event handlers: onclick=, onerror=, onload=, ...
        $html = preg_replace('#\son[a-z-]+\s*=\s*"[^"]*"#is', '', $html) ?? $html;
        $html = preg_replace("#\son[a-z-]+\s*=\s*'[^']*'#is", '', $html) ?? $html;
        $html = preg_replace('#\son[a-z-]+\s*=\s*[^\s>]+#is', '', $html) ?? $html;

        // 3. Neutralise executable URL schemes in href/src/action attributes.
        $html = preg_replace(
            '#\b(href|src|action|background)\s*=\s*(["\']?)\s*(javascript|vbscript|data)\s*:[^"\'>\s]*\2#is',
            '$1="#"',
            $html
        ) ?? $html;

        return trim($html);
    }

    /**
     * Readable plain text from HTML — used for the email's text alternative and
     * for list previews.
     */
    public static function toText(?string $html): string
    {
        $text = preg_replace('#<(br|/p|/div|/li|/h[1-6])\s*/?>#i', "\n", (string) $html) ?? (string) $html;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Collapse the runs of blank lines the tag stripping leaves behind.
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }
}
