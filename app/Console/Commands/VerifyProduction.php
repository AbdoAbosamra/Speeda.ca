<?php

namespace App\Console\Commands;

use App\Support\UploadLimits;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyProduction extends Command
{
    protected $signature = 'deploy:verify';

    protected $description = 'Validate production configuration before activating a release';

    public function handle(): int
    {
        $failures = [];
        if (! app()->isProduction()) {
            $failures[] = 'APP_ENV must be production';
        }
        if (config('app.debug') !== false) {
            $failures[] = 'APP_DEBUG must be false';
        }
        if (! config('session.encrypt')) {
            $failures[] = 'SESSION_ENCRYPT must be true';
        }
        if (! config('session.secure')) {
            $failures[] = 'SESSION_SECURE_COOKIE must be true on an HTTPS site';
        }
        if (! str_starts_with((string) config('app.url'), 'https://') || in_array(parse_url(config('app.url'), PHP_URL_HOST), ['localhost', '127.0.0.1', '::1'])) {
            $failures[] = 'APP_URL must be a public HTTPS URL';
        }
        if (! config('app.analytics_salt') || config('app.analytics_salt') === 'default-salt-change-in-production') {
            $failures[] = 'Set ANALYTICS_SALT or a valid APP_KEY';
        }
        if (! str_starts_with((string) config('view.compiled'), base_path().DIRECTORY_SEPARATOR)) {
            $failures[] = 'VIEW_COMPILED_PATH must be inside the release';
        }
        // A gallery image is allowed to be 10 MB, so a server that caps uploads
        // below that cannot accept a file the product promises to take — and
        // the person uploading only ever sees "failed to upload". This is a
        // hard requirement, not a preference.
        if (UploadLimits::phpFileMb() < UploadLimits::MAX_FILE_MB) {
            $failures[] = sprintf(
                'upload_max_filesize is %sM but a gallery image may be %dM',
                self::mb(UploadLimits::phpFileMb()),
                UploadLimits::MAX_FILE_MB
            );
        }

        // One max-size file still has to fit alongside the rest of the form.
        // Below this, a single valid upload is dropped whole.
        $minRequestMb = UploadLimits::phpFileMb() + 8;
        if (UploadLimits::phpRequestMb() < $minRequestMb) {
            $failures[] = sprintf(
                'post_max_size is %sM, too small to carry one %sM upload plus the form around it (needs %sM)',
                self::mb(UploadLimits::phpRequestMb()),
                self::mb(UploadLimits::phpFileMb()),
                self::mb($minRequestMb)
            );
        }

        // Filling every upload field at once exceeds most default limits. The
        // browser-side guard stops that submit with a "upload in batches"
        // message rather than losing the request, so this is worth saying out
        // loud without blocking a release over it.
        if (UploadLimits::phpRequestMb() < UploadLimits::MAX_REQUEST_MB) {
            $this->warn(sprintf(
                'post_max_size is %sM; filling every upload field in one submit can reach %dM. '
                .'Admins will be asked to upload in smaller batches.',
                self::mb(UploadLimits::phpRequestMb()),
                UploadLimits::MAX_REQUEST_MB
            ));
        }

        // Not blocking, but worth seeing on every deploy: with these values
        // emails are silently never delivered.
        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            $this->warn('MAIL_MAILER is "'.config('mail.default').'": no email will actually be delivered.');
        }

        if (! is_writable(storage_path('app/public'))) {
            $failures[] = 'storage/app/public must be writable (uploads land here)';
        }

        try {
            DB::select('select 1');
        } catch (\Throwable $e) {
            $failures[] = 'Database is unreachable';
        }
        foreach ($failures as $failure) {
            $this->error($failure);
        }
        if ($failures) {
            return self::FAILURE;
        }
        $this->info('Production configuration verified.');

        return self::SUCCESS;
    }

    /**
     * Megabytes without trailing zeroes, so messages read "20M" not "20.0M".
     */
    private static function mb(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1), '0'), '.');
    }
}
