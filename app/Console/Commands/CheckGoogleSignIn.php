<?php

namespace App\Console\Commands;

use App\Services\GoogleAuthService;
use Illuminate\Console\Command;

/**
 * Prints the exact redirect URI this deployment sends to Google.
 *
 * `redirect_uri_mismatch` is the one Google error that cannot be diagnosed
 * from the browser: the rejected URI is the one Laravel sent, not the one the
 * user typed, and Google compares it to the console entry byte for byte.
 * `localhost` and `127.0.0.1` are different hosts to Google, `:8000` and
 * `:8001` are different origins, and a trailing slash is a different URI.
 * Rather than guess which of those it is, print the string and let it be
 * pasted into the console verbatim.
 */
class CheckGoogleSignIn extends Command
{
    protected $signature = 'speeda:google-check';

    protected $description = 'Show the exact Google sign-in redirect URI this app sends, and flag the usual mismatches.';

    public function handle(): int
    {
        $clientId = (string) config('services.google.client_id');
        $redirect = (string) config('services.google.redirect');
        $appUrl = rtrim((string) config('app.url'), '/');

        $this->newLine();
        $this->line('  <options=bold>Google sign-in</>');
        $this->newLine();

        if (!GoogleAuthService::isEnabled()) {
            $this->error('  Disabled: GOOGLE_CLIENT_ID and/or GOOGLE_CLIENT_SECRET are not set.');
            $this->line('  The button is hidden and /auth/google/* returns 404 until both are filled in.');
            $this->newLine();

            return self::FAILURE;
        }

        $this->line('  Client ID   ' . $clientId);
        $this->line('  APP_URL     ' . $appUrl);
        $this->newLine();
        $this->line('  <options=bold>Paste this into Google Cloud Console -> Credentials -> your OAuth');
        $this->line('  client -> Authorized redirect URIs, character for character:</>');
        $this->newLine();
        $this->line('    <fg=green>' . $redirect . '</>');
        $this->newLine();

        $origin = $this->originOf($redirect);

        if ($origin) {
            $this->line('  And this one under Authorized JavaScript origins:');
            $this->newLine();
            $this->line('    <fg=green>' . $origin . '</>');
            $this->newLine();
        }

        $warnings = $this->warnings($redirect, $appUrl, $origin);

        foreach ($warnings as $warning) {
            $this->warn('  ! ' . $warning);
        }

        if ($warnings) {
            $this->newLine();

            return self::FAILURE;
        }

        $this->info('  Nothing looks wrong on this side. If Google still says');
        $this->info('  redirect_uri_mismatch, the console entry does not match the URI above.');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function warnings(string $redirect, string $appUrl, ?string $origin): array
    {
        $warnings = [];

        if (!str_ends_with($redirect, '/auth/google/callback')) {
            $warnings[] = 'GOOGLE_REDIRECT_URI does not end in /auth/google/callback, which is the route that exists.';
        }

        if ($origin && $appUrl !== '' && $origin !== $appUrl) {
            $warnings[] = "GOOGLE_REDIRECT_URI points at {$origin} but APP_URL is {$appUrl}. "
                . 'Google is sent the first one; the session cookie is set by whichever host the '
                . 'browser is actually on, so serve the site from one of them and make both agree.';
        }

        if (str_starts_with($redirect, 'http://') && !$this->isLoopback($redirect)) {
            $warnings[] = 'GOOGLE_REDIRECT_URI is plain http on a non-loopback host. Google only accepts http for localhost/127.0.0.1.';
        }

        if (file_exists(base_path('bootstrap/cache/config.php'))) {
            $warnings[] = 'The config cache is warm, so a .env edit will not be picked up until `php artisan config:clear`.';
        }

        return $warnings;
    }

    private function originOf(string $url): ?string
    {
        $parts = parse_url($url);

        if (empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        return $parts['scheme'] . '://' . $parts['host']
            . (isset($parts['port']) ? ':' . $parts['port'] : '');
    }

    private function isLoopback(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }
}
