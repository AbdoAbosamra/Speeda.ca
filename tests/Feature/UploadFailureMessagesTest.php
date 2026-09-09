<?php

namespace Tests\Feature;

use App\Http\Middleware\ExplainUploadFailures;
use App\Support\UploadLimits;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * PHP rejects an oversized or interrupted upload before the application sees
 * it, and Laravel's validator then reports every one of those causes with the
 * same sentence — "The gallery_images.0 failed to upload" — which tells nobody
 * whether to pick a smaller file, retry, or call an administrator.
 *
 * These pin the replacement: the cause is named, the field is named the way it
 * appears on screen, and a normal empty file input still passes through
 * untouched.
 */
class UploadFailureMessagesTest extends TestCase
{
    /**
     * Builds the failed upload PHP hands over: a file object carrying an error
     * code instead of a usable temp file.
     */
    private function failedUpload(int $error): UploadedFile
    {
        return new UploadedFile(
            __FILE__,
            'holiday-photo.jpg',
            'image/jpeg',
            $error,
            true, // test mode: isValid() then reflects the error code
        );
    }

    private function runMiddleware(array $files): ?ValidationException
    {
        $request = Request::create('/admin/providers/10', 'POST', [], [], $files);

        try {
            (new ExplainUploadFailures)->handle($request, fn () => response('ok'));
        } catch (ValidationException $e) {
            return $e;
        }

        return null;
    }

    public function test_an_oversized_file_says_so_and_names_the_real_limit(): void
    {
        $e = $this->runMiddleware(['profile_image' => $this->failedUpload(UPLOAD_ERR_INI_SIZE)]);

        $this->assertNotNull($e);
        $message = $e->errors()['profile_image'][0];

        $this->assertStringContainsString('larger than this server accepts', $message);
        // The number quoted has to be PHP's, not the form's — quoting the
        // form's limit is what makes the error look like a lie.
        $this->assertStringContainsString(
            rtrim(rtrim(number_format(UploadLimits::phpFileMb(), 1), '0'), '.'),
            $message
        );
        $this->assertStringContainsString('profile photo', $message);
    }

    public function test_an_interrupted_upload_asks_for_a_retry_rather_than_a_smaller_file(): void
    {
        $e = $this->runMiddleware(['profile_image' => $this->failedUpload(UPLOAD_ERR_PARTIAL)]);

        $this->assertNotNull($e);
        $this->assertStringContainsString('try again', strtolower($e->errors()['profile_image'][0]));
    }

    public function test_a_server_side_failure_is_not_blamed_on_the_person_uploading(): void
    {
        $e = $this->runMiddleware(['profile_image' => $this->failedUpload(UPLOAD_ERR_CANT_WRITE)]);

        $this->assertNotNull($e);
        $message = $e->errors()['profile_image'][0];

        $this->assertStringContainsString('server problem', $message);
        $this->assertStringNotContainsString('smaller', $message);
    }

    public function test_a_failed_gallery_image_is_numbered_the_way_it_is_shown(): void
    {
        $e = $this->runMiddleware([
            'gallery_images' => [
                $this->failedUpload(UPLOAD_ERR_OK),
                $this->failedUpload(UPLOAD_ERR_INI_SIZE),
            ],
        ]);

        $this->assertNotNull($e);
        $this->assertArrayHasKey('gallery_images.1', $e->errors());
        // Second in the array is the second image on screen, not "1".
        $this->assertStringContainsString('gallery image #2', $e->errors()['gallery_images.1'][0]);
        $this->assertArrayNotHasKey('gallery_images.0', $e->errors());
    }

    public function test_a_form_with_no_failed_uploads_passes_straight_through(): void
    {
        $this->assertNull($this->runMiddleware([
            'profile_image' => UploadedFile::fake()->image('fine.jpg'),
        ]));

        $this->assertNull($this->runMiddleware([]));
    }

    /**
     * An untouched file input reaches PHP as UPLOAD_ERR_NO_FILE. Symfony turns
     * that into null before validation, which is the whole reason `nullable`
     * works on these fields — so nothing here should object to it either.
     */
    public function test_an_untouched_file_input_is_not_treated_as_a_failure(): void
    {
        // Exactly what PHP puts in $_FILES for a file input the person never
        // touched: a part with no name, no size and UPLOAD_ERR_NO_FILE.
        $this->assertNull($this->runMiddleware([
            'profile_image' => [
                'name' => '',
                'type' => '',
                'tmp_name' => '',
                'error' => UPLOAD_ERR_NO_FILE,
                'size' => 0,
            ],
        ]));
    }

    public function test_the_advertised_limit_never_exceeds_what_php_accepts(): void
    {
        $this->assertLessThanOrEqual(UploadLimits::phpFileMb(), UploadLimits::effectiveMb(10));
        $this->assertLessThanOrEqual(10, UploadLimits::effectiveMb(10));
    }

    public function test_php_size_shorthand_is_read_correctly(): void
    {
        $this->assertSame(2 * 1024 * 1024, UploadLimits::toBytes('2M'));
        $this->assertSame(512 * 1024, UploadLimits::toBytes('512K'));
        $this->assertSame(1024 * 1024 * 1024, UploadLimits::toBytes('1G'));
        $this->assertSame(1024, UploadLimits::toBytes('1024'));
        // "no limit" and "unset" must not read as "zero bytes allowed".
        $this->assertSame(0, UploadLimits::toBytes('-1'));
        $this->assertSame(0, UploadLimits::toBytes(''));
    }
}
