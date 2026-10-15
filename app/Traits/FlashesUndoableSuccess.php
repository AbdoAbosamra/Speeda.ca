<?php

namespace App\Traits;

use App\Helpers\ErrorHelper;
use App\Models\AdminLog;

trait FlashesUndoableSuccess
{
    /**
     * Flash success message with undo link
     */
    protected function flashSuccessWithUndo(string $message, AdminLog $log)
    {
        // The undo affordance is rendered by <x-error-handler /> from the log id,
        // so the flash payload stays plain text (no HTML injected into the session).
        ErrorHelper::flashNotification($message, 'success', $log->id);
    }
}
