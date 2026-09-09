<?php

namespace App\Http\Middleware;

use App\Support\UploadLimits;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Turns a PHP-level upload failure into an error that says what went wrong.
 *
 * When PHP rejects a file — too big for upload_max_filesize, connection cut
 * mid-transfer, no writable temp directory — the file still reaches the
 * application, but as an UploadedFile that is not valid. Laravel's validator
 * spots that first (Validator::validateAttribute) and fails the field with its
 * generic `uploaded` message, before `nullable`, `max` or `mimes` ever run. The
 * result is "The gallery_images.0 could not be uploaded" for six unrelated
 * causes, with nothing pointing at which one it was.
 *
 * Running ahead of validation lets each cause say its own name, and puts the
 * real PHP error code in the log so a server-side problem (an unwritable temp
 * dir, say) is diagnosable instead of looking like user error.
 *
 * UPLOAD_ERR_NO_FILE never appears here: Symfony's FileBag already turns an
 * untouched file input into null, which is what makes `nullable` work.
 */
class ExplainUploadFailures
{
    public function handle(Request $request, Closure $next): Response
    {
        $failures = [];

        foreach (Arr::dot($request->allFiles()) as $field => $file) {
            if (! $file instanceof UploadedFile || $file->isValid()) {
                continue;
            }

            $failures[$field] = $this->describe($file, $field);

            $context = [
                'field' => $field,
                'php_error' => $file->getError(),
                'reason' => $file->getErrorMessage(),
                'client_name' => $file->getClientOriginalName(),
                'route' => $request->path(),
            ];

            // A file too big for the server is the person's side of the line
            // and belongs at warning. No writable temp directory is the
            // server's side, breaks every upload for everyone, and has to
            // survive a production LOG_LEVEL that keeps only errors.
            if (in_array($file->getError(), [UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE], true)) {
                Log::error('[Upload] Server cannot store uploads', $context);
            } else {
                Log::warning('[Upload] Rejected by PHP before validation', $context);
            }
        }

        if ($failures !== []) {
            throw ValidationException::withMessages($failures);
        }

        return $next($request);
    }

    /**
     * One message per PHP upload error, named for what the person can do next.
     */
    private function describe(UploadedFile $file, string $field): string
    {
        $name = $this->label($field);

        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => __('validation.upload_too_large', [
                'attribute' => $name,
                'max' => rtrim(rtrim(number_format(UploadLimits::phpFileMb(), 1), '0'), '.'),
            ]),
            UPLOAD_ERR_PARTIAL => __('validation.upload_interrupted', ['attribute' => $name]),
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => __('validation.upload_server_error', ['attribute' => $name]),
            UPLOAD_ERR_EXTENSION => __('validation.upload_blocked', ['attribute' => $name]),
            default => __('validation.upload_failed', ['attribute' => $name]),
        };
    }

    /**
     * "gallery_images.0" is an implementation detail; people counting images on
     * screen are looking at image 1.
     */
    private function label(string $field): string
    {
        $parts = explode('.', $field);
        $base = __('validation.attributes.'.$parts[0]);

        if ($base === 'validation.attributes.'.$parts[0]) {
            $base = str_replace('_', ' ', $parts[0]);
        }

        return isset($parts[1]) && is_numeric($parts[1])
            ? $base.' #'.((int) $parts[1] + 1)
            : $base;
    }
}
