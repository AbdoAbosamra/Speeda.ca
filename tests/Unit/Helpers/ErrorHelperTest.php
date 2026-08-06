<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\Attributes\Test;

use App\Helpers\ErrorHelper;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ErrorHelperTest extends TestCase
{
    #[Test]
    public function it_handles_validation_exceptions()
    {
        $validationException = ValidationException::withMessages([
            'email' => ['The email field is required.']
        ]);

        $result = ErrorHelper::handle($validationException);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertFalse($result['success']);
        $this->assertIsArray($result['errors']);
    }

    #[Test]
    public function it_handles_http_exceptions()
    {
        $httpException = new HttpException(404, 'Not Found');

        $result = ErrorHelper::handle($httpException);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('404', $result['message']);
    }

    #[Test]
    public function it_handles_general_exceptions()
    {
        $generalException = new \Exception('Something went wrong');

        $result = ErrorHelper::handle($generalException);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertFalse($result['success']);
    }

    #[Test]
    public function it_accepts_custom_error_messages()
    {
        $exception = new \Exception('Original message');
        $customMessage = 'Custom error message';

        $result = ErrorHelper::handle($exception, $customMessage);

        $this->assertEquals($customMessage, $result['message']);
    }
}
