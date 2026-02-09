<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Category;
use App\Models\Location;
use App\Helpers\ErrorHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UndoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Undo an admin action based on the log.
     */
    public function undo(AdminLog $log)
    {
        try {
            // Check time limit (e.g. 24 hours)
            if ($log->created_at->diffInHours(now()) > 24) {
                return redirect()->back()->with('error', __('admin.undo_time_limit_exceeded'));
            }

            // Check if reversible
            if (!in_array($log->action, ['create', 'update', 'delete', 'deactivate', 'activate'])) {
                return redirect()->back()->with('error', __('admin.cannot_undo_action'));
            }

            DB::transaction(function () use ($log) {
                $modelClass = $log->model_type;
                $modelId = $log->model_id;

                if (!class_exists($modelClass)) {
                    throw new \Exception("Model class {$modelClass} not found");
                }

                switch ($log->action) {
                    case 'create':
                        // Undo create = delete (or deactivate if safer)
                        // Requirement: "Category becomes inactive"
                        $model = $modelClass::find($modelId);
                        if ($model) {
                            if (method_exists($model, 'update') && \Schema::hasColumn($model->getTable(), 'is_active')) {
                                $model->update(['is_active' => false]);
                            } else {
                                $model->delete();
                            }
                        }
                        break;

                    case 'update':
                        // Undo update = restore 'before' values
                        $model = $modelClass::find($modelId);
                        if ($model && isset($log->changes['before'])) {
                            $model->update($log->changes['before']);
                        }
                        break;

                    case 'delete':
                        // Undo delete = restore
                        // If soft deleted, restore()
                        // If hard deleted, we might not be able to fully restore unless we stored all data
                        // AdminLog 'deleted' key has the data, but re-inserting with same ID is tricky
                        // For now, assume soft deletes or manual re-creation if critical
                        if (method_exists($modelClass, 'withTrashed')) {
                            $model = $modelClass::withTrashed()->find($modelId);
                            if ($model && $model->trashed()) {
                                $model->restore();
                            }
                        }
                        break;

                    case 'deactivate':
                        // Undo deactivate = activate
                        $model = $modelClass::find($modelId);
                        if ($model)
                            $model->update(['is_active' => true]);
                        break;

                    case 'activate':
                        // Undo activate = deactivate
                        $model = $modelClass::find($modelId);
                        if ($model)
                            $model->update(['is_active' => false]);
                        break;
                }

                // Mark the original log as undone
                $log->update(['is_undone' => true]);

                // Log the undo action itself
                AdminLog::log(
                    'undo',
                    $modelClass,
                    $modelId,
                    "Undid action #{$log->id} ({$log->action})"
                );
            });

            ErrorHelper::flashNotification(__('admin.action_undone_successfully'), 'success');
            return redirect()->back();

        } catch (\Exception $e) {
            Log::error('Undo failed', ['error' => $e->getMessage(), 'log_id' => $log->id]);
            ErrorHelper::flashNotification(__('admin.undo_failed') . ': ' . $e->getMessage(), 'error');
            return redirect()->back();
        }
    }
}
