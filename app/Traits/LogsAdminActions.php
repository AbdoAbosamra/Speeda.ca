<?php

namespace App\Traits;

use App\Models\AdminLog;
use Illuminate\Database\Eloquent\Model;

trait LogsAdminActions
{
    /**
     * Log a create action.
     */
    protected function logCreate(Model $model, ?string $name = null): AdminLog
    {
        return AdminLog::log(
            'create',
            get_class($model),
            $model->id,
            $name ?? $this->getModelName($model),
            ['created' => $model->toArray()]
        );
    }

    /**
     * Log an update action with changes.
     */
    protected function logUpdate(Model $model, array $oldValues, ?string $name = null): AdminLog
    {
        $changes = [
            'before' => array_intersect_key($oldValues, $model->getChanges()),
            'after' => $model->getChanges(),
        ];

        return AdminLog::log(
            'update',
            get_class($model),
            $model->id,
            $name ?? $this->getModelName($model),
            $changes
        );
    }

    /**
     * Log a delete action.
     */
    protected function logDelete(Model $model, ?string $name = null): AdminLog
    {
        return AdminLog::log(
            'delete',
            get_class($model),
            $model->id,
            $name ?? $this->getModelName($model),
            ['deleted' => $model->toArray()]
        );
    }

    /**
     * Log an approval action.
     */
    protected function logApprove(Model $model, ?string $name = null): AdminLog
    {
        return AdminLog::log(
            'approve',
            get_class($model),
            $model->id,
            $name ?? $this->getModelName($model)
        );
    }

    /**
     * Log a rejection action.
     */
    protected function logReject(Model $model, ?string $reason = null, ?string $name = null): AdminLog
    {
        return AdminLog::log(
            'reject',
            get_class($model),
            $model->id,
            $name ?? $this->getModelName($model),
            $reason ? ['reason' => $reason] : null
        );
    }

    /**
     * Log a generic admin action.
     */
    protected function logAction(string $action, ?Model $model = null, ?array $data = null, ?string $name = null): AdminLog
    {
        return AdminLog::log(
            $action,
            $model ? get_class($model) : null,
            $model?->id,
            $name ?? ($model ? $this->getModelName($model) : null),
            $data
        );
    }

    /**
     * Get a human-readable name for the model.
     */
    protected function getModelName(Model $model): string
    {
        // Try common name fields
        foreach (['name', 'title', 'city', 'company_name', 'content'] as $field) {
            if (isset($model->$field)) {
                return is_string($model->$field) ? substr($model->$field, 0, 100) : (string) $model->$field;
            }
        }

        return class_basename($model) . ' #' . $model->id;
    }
}
