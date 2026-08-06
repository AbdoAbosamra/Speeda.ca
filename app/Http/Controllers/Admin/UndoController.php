<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Helpers\ErrorHelper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
            // An undo may only ever be applied once; without this a repeated POST
            // (back button, double submit) would re-apply the reversal.
            if ($log->is_undone) {
                return redirect()->back()->with('warning', __('admin.action_already_undone'));
            }

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
                        // Undo create = deactivate when possible (safer than
                        // destroying a row that may already have references),
                        // otherwise delete.
                        $model = $modelClass::find($modelId);
                        if (!$model) {
                            throw new \Exception(__('admin.undo_target_missing'));
                        }

                        if (Schema::hasColumn($model->getTable(), 'is_active')) {
                            $model->update(['is_active' => false]);
                        } else {
                            $model->delete();
                        }
                        break;

                    case 'update':
                        // Undo update = restore 'before' values
                        $model = $modelClass::find($modelId);
                        if (!$model) {
                            throw new \Exception(__('admin.undo_target_missing'));
                        }
                        if (empty($log->changes['before'])) {
                            throw new \Exception(__('admin.undo_no_previous_values'));
                        }
                        $model->update($log->changes['before']);
                        break;

                    case 'delete':
                        // Only soft-deleting models can be brought back. For a hard
                        // delete we fail loudly instead of reporting a success the
                        // admin can't verify.
                        if (!$this->usesSoftDeletes($modelClass)) {
                            throw new \Exception(__('admin.undo_hard_delete_unsupported', [
                                'model' => class_basename($modelClass),
                            ]));
                        }

                        $model = $modelClass::withTrashed()->find($modelId);
                        if (!$model) {
                            throw new \Exception(__('admin.undo_target_missing'));
                        }

                        if ($model->trashed()) {
                            $model->restore();
                        }

                        // A trashed record is also deactivated on the way out
                        // (see AdminController::deleteUser), so bring it back live.
                        if (Schema::hasColumn($model->getTable(), 'is_active')) {
                            $model->update(['is_active' => true]);
                        }
                        break;

                    case 'deactivate':
                    case 'activate':
                        $model = $modelClass::find($modelId);
                        if (!$model) {
                            throw new \Exception(__('admin.undo_target_missing'));
                        }
                        $model->update(['is_active' => $log->action === 'deactivate']);
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

    /**
     * Does the given model class support soft deletes (and therefore restore)?
     */
    private function usesSoftDeletes(string $modelClass): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($modelClass), true);
    }
}
