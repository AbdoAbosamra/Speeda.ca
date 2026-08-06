<?php

namespace App\Traits;

use App\Helpers\ErrorHelper;
use App\Support\BulkActionResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Shared plumbing for "select rows, apply an action to all of them" screens.
 *
 * Design rules this enforces for every module:
 *
 *  1. The action name is whitelisted per resource — never taken on trust.
 *  2. Each record is processed individually through the SAME guards as the
 *     single-item route. Bulk is a convenience, never a way to bypass a rule
 *     like "you cannot deactivate the last admin".
 *  3. A failure on one record does not abort the batch; it is recorded as a
 *     skip with a reason and the rest continue.
 *  4. The admin always gets an honest count of what actually happened.
 *
 * A controller can serve several resources (AdminController handles users,
 * categories and locations), hence the $resource argument threaded through.
 */
trait HandlesBulkActions
{
    /**
     * Allowed action => PAST-TENSE verb used in the result message.
     *
     * The value reads as "12 items <verb>." so it must be e.g. "approved",
     * not "Approve". Button captions are defined separately in the view.
     *
     * @return array<string, string>
     */
    abstract protected function bulkActions(string $resource): array;

    /**
     * Base query used to resolve the selected ids.
     */
    abstract protected function bulkQuery(string $resource): Builder;

    /**
     * Apply one action to one record.
     *
     * Return true on success, or a string describing why it was skipped.
     *
     * @return true|string
     */
    abstract protected function applyBulkAction(string $resource, string $action, $model);

    /**
     * Validate + run a bulk request, then redirect back with a summary.
     */
    protected function runBulkAction(Request $request, string $resource)
    {
        $allowed = $this->bulkActions($resource);

        $validated = $request->validate([
            'bulk_action' => ['required', 'string', 'in:' . implode(',', array_keys($allowed))],
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => __('admin.bulk_nothing_selected'),
            'ids.max' => __('admin.bulk_too_many'),
        ]);

        $action = $validated['bulk_action'];
        $ids = array_values(array_unique(array_map('intval', $validated['ids'])));

        $records = $this->bulkQuery($resource)->whereIn($this->bulkKeyName(), $ids)->get();
        $found = $records->map(fn ($r) => (int) $r->getKey())->all();

        $result = new BulkActionResult();

        // Ids the query could not resolve (deleted meanwhile, or out of scope).
        foreach (array_diff($ids, $found) as $missingId) {
            $result->skipped($missingId, __('admin.bulk_reason_not_found'));
        }

        foreach ($records as $record) {
            try {
                // Per-record transaction: one bad row cannot roll back the batch.
                $outcome = DB::transaction(fn () => $this->applyBulkAction($resource, $action, $record));

                $outcome === true
                    ? $result->succeeded()
                    : $result->skipped($record->getKey(), is_string($outcome) ? $outcome : __('admin.bulk_reason_failed'));
            } catch (\Throwable $e) {
                Log::error('Bulk action failed for a record', [
                    'resource' => $resource,
                    'action' => $action,
                    'model' => get_class($record),
                    'id' => $record->getKey(),
                    'error' => $e->getMessage(),
                ]);

                $result->skipped($record->getKey(), __('admin.bulk_reason_failed'));
            }
        }

        Log::info('Bulk admin action executed', [
            'resource' => $resource,
            'action' => $action,
            'requested' => count($ids),
            'succeeded' => $result->successCount(),
            'skipped' => $result->skippedCount(),
            'admin_id' => auth()->id(),
        ]);

        ErrorHelper::flashNotification(
            $result->message($allowed[$action]),
            $result->flashType()
        );

        return redirect()->back();
    }

    /**
     * Primary key column used to match selected ids. Override when needed.
     */
    protected function bulkKeyName(): string
    {
        return 'id';
    }
}
