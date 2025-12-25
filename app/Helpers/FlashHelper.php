<?php

namespace App\Helpers;

class FlashHelper
{
    /**
     * Flash a success message
     *
     * @param string $message
     * @param bool $useToast Use toast notification instead of alert banner
     * @return void
     */
    public static function success(string $message, bool $useToast = false): void
    {
        session()->flash($useToast ? 'toast_success' : 'success', $message);
    }

    /**
     * Flash an error message
     *
     * @param string $message
     * @param bool $useToast Use toast notification instead of alert banner
     * @return void
     */
    public static function error(string $message, bool $useToast = false): void
    {
        session()->flash($useToast ? 'toast_error' : 'error', $message);
    }

    /**
     * Flash a warning message
     *
     * @param string $message
     * @param bool $useToast Use toast notification instead of alert banner
     * @return void
     */
    public static function warning(string $message, bool $useToast = false): void
    {
        session()->flash($useToast ? 'toast_warning' : 'warning', $message);
    }

    /**
     * Flash an info message
     *
     * @param string $message
     * @param bool $useToast Use toast notification instead of alert banner
     * @return void
     */
    public static function info(string $message, bool $useToast = false): void
    {
        session()->flash($useToast ? 'toast_info' : 'info', $message);
    }

    /**
     * Flash a validation error message
     *
     * @param array|string $errors
     * @return void
     */
    public static function validationError($errors): void
    {
        if (is_array($errors)) {
            session()->flash('error', __('validation.please_correct_errors'));
        } else {
            session()->flash('error', $errors);
        }
    }
}
