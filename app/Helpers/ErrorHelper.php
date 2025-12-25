<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ErrorHelper
{
    /**
     * Handle exceptions and return user-friendly error messages
     *
     * @param \Exception $exception
     * @param string|null $customMessage
     * @return array
     */
    public static function handle(\Exception $exception, ?string $customMessage = null): array
    {
        // Log the full exception for debugging
        Log::error('Error occurred: ' . $exception->getMessage(), [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Extract user-friendly message and error details
        $message = $customMessage ?? self::getUserFriendlyMessage($exception);
        $type = 'error';

        // Prepare response array
        $response = [
            'success' => false,
            'message' => $message,
            'type' => $type,
        ];

        // Add validation errors if it's a ValidationException
        if ($exception instanceof ValidationException) {
            $response['errors'] = $exception->errors();
        } else {
            $response['errors'] = [];
        }

        return $response;
    }

    /**
     * Get user-friendly error message based on exception type
     *
     * @param \Exception $exception
     * @return string
     */
    protected static function getUserFriendlyMessage(\Exception $exception): string
    {
        // Handle validation exceptions
        if ($exception instanceof ValidationException) {
            $errors = $exception->errors();
            $firstError = reset($errors);
            return is_array($firstError) ? $firstError[0] : $firstError;
        }

        // Handle HTTP exceptions
        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
            return match($statusCode) {
                404 => "404 - The requested resource was not found.",
                403 => "403 - Access forbidden.",
                401 => "401 - Unauthorized access.",
                419 => "419 - CSRF token has expired.",
                500 => "500 - Internal server error.",
                default => "{$statusCode} - HTTP error occurred.",
            };
        }

        // Handle database exceptions
        if ($exception instanceof \Illuminate\Database\QueryException) {
            // Check for specific database errors
            $errorCode = $exception->errorInfo[1] ?? null;

            if ($errorCode === 1062) { // Duplicate entry
                return __('errors.duplicate_entry');
            }

            if ($errorCode === 1452) { // Foreign key constraint
                return __('errors.foreign_key_constraint');
            }

            return __('errors.database_error');
        }

        // Handle file upload exceptions
        if ($exception instanceof \Symfony\Component\HttpFoundation\File\Exception\FileException) {
            return __('errors.file_upload_error');
        }

        // Default generic error
        return __('errors.generic_error');
    }

    /**
     * Create error notification array for session flash
     *
     * @param string $message
     * @param string $type (success, error, warning, info)
     * @return array
     */
    public static function createNotification(string $message, string $type = 'error'): array
    {
        return [
            'message' => $message,
            'type' => $type,
            'icon' => self::getIconForType($type),
        ];
    }

    /**
     * Get icon class for notification type
     *
     * @param string $type
     * @return string
     */
    protected static function getIconForType(string $type): string
    {
        return match($type) {
            'success' => 'fas fa-check-circle',
            'error' => 'fas fa-exclamation-circle',
            'warning' => 'fas fa-exclamation-triangle',
            'info' => 'fas fa-info-circle',
            default => 'fas fa-info-circle',
        };
    }

    /**
     * Flash error notification to session
     *
     * @param string $message
     * @param string $type
     * @return void
     */
    public static function flashNotification(string $message, string $type = 'error'): void
    {
        session()->flash('notification', self::createNotification($message, $type));
    }

    /**
     * Handle CSRF token mismatch
     *
     * @return array
     */
    public static function handleCsrfMismatch(): array
    {
        return self::createNotification(
            __('errors.csrf_token_expired_message'),
            'error'
        );
    }
}
