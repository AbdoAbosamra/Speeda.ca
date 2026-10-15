<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ErrorHelper;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use App\Traits\ClearsAdminCaches;
use App\Traits\FlashesUndoableSuccess;
use App\Traits\HandlesBulkActions;
use App\Traits\LogsAdminActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminLocationController extends Controller
{
    use LogsAdminActions;
    use HandlesBulkActions;
    use ClearsAdminCaches;
    use FlashesUndoableSuccess;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display list of all locations (only active ones)
     */
    public function locations()
    {
        try {
            // Show all locations to admin (active + inactive), ordering active ones first
            $locations = Location::orderByDesc('is_active')->orderBy('city')->paginate(20)->withQueryString();
            return view('admin.locations.index', compact('locations'));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->route('admin.dashboard');
        }
    }

    /**
     * Store a new location
     */
    public function storeLocation(StoreLocationRequest $request)
    {
        try {
            $validated = $request->validated();

            return DB::transaction(function () use ($request, $validated) {
                $imagePath = null;
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $filename = 'location_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $image->getClientOriginalExtension();
                    $imagePath = $image->storeAs('location-images', $filename, 'public');
                }

                $location = Location::create([
                    'city' => $validated['city'],
                    'country' => $validated['country'] ?? null,
                    'area' => $validated['area'] ?? null,
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                    'image' => $imagePath,
                    'is_active' => $validated['is_active'] ?? true,
                    'meta_title' => $validated['meta_title'] ?? null,
                    'meta_description' => $validated['meta_description'] ?? null,
                ]);

                Log::info('Location created by admin', [
                    'location_id' => $location->id,
                    'city' => $location->city,
                    'admin_id' => Auth::id(),
                ]);

                $log = $this->logAction('create', $location);
                $this->clearApplicationCaches();

                $this->flashSuccessWithUndo(__('admin.location_created_successfully'), $log);

                return redirect()->route('admin.locations');
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Update a location
     */
    public function updateLocation(UpdateLocationRequest $request, Location $location)
    {
        try {
            $validated = $request->validated();

            return DB::transaction(function () use ($request, $validated, $location) {
                // Nullable fields are only touched when the form actually sent
                // them, so a cleared input clears the column and an absent input
                // leaves the stored value alone.
                $updateData = [
                    'city' => $validated['city'],
                    'is_active' => $validated['is_active'] ?? $location->is_active,
                ];

                foreach (['country', 'area', 'latitude', 'longitude', 'meta_title', 'meta_description'] as $nullable) {
                    if (array_key_exists($nullable, $validated)) {
                        $updateData[$nullable] = $validated[$nullable];
                    }
                }

                // Handle image upload
                if ($request->hasFile('image')) {
                    // Note: We do NOT delete the old image here to allow "Undo" functionality to work.
                    // If we delete it, restoring the old path in DB would result in a broken image.
                    // Old images can be cleaned up via a scheduled job if needed.
                    /*
                    if ($location->image && Storage::disk('public')->exists($location->image)) {
                        Storage::disk('public')->delete($location->image);
                    }
                    */

                    $image = $request->file('image');
                    $filename = 'location_' . $location->id . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $image->getClientOriginalExtension();
                    $imagePath = $image->storeAs('location-images', $filename, 'public');
                    $updateData['image'] = $imagePath;
                }


                $oldValues = $location->getOriginal(); // Capture old values BEFORE update
                $location->update($updateData);

                $log = $this->logUpdate($location, $oldValues);
                $this->clearApplicationCaches();

                $this->flashSuccessWithUndo(__('admin.location_updated_successfully'), $log);

                return redirect()->route('admin.locations');
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Delete a location
     */
    public function deleteLocation(Location $location)
    {
        try {
            // Workflow: Active -> Deactivate -> Delete
            if ($location->is_active) {
                ErrorHelper::flashNotification(
                    __('admin.cannot_delete_active_location'),
                    'error'
                );
                return redirect()->back();
            }

            // Check if location has service providers
            $providersCount = $location->serviceProviders()->count();
            if ($providersCount > 0) {
                ErrorHelper::flashNotification(
                    __('admin.location_has_providers', ['count' => $providersCount]),
                    'error'
                );
                return redirect()->back();
            }

            return DB::transaction(function () use ($location) {
                $cityName = $location->city;

                // Delete location image if exists
                if ($location->image && Storage::disk('public')->exists($location->image)) {
                    Storage::disk('public')->delete($location->image);
                }

                $log = $this->logAction('delete', $location, ['deleted' => $location->toArray()]);
                $location->delete();
                $this->clearApplicationCaches();

                $this->flashSuccessWithUndo(__('admin.location_deleted_successfully', ['city' => $cityName]), $log);

                return redirect()->route('admin.locations');
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Deactivate a location
     */
    public function deactivateLocation(Location $location)
    {
        try {
            $location->update(['is_active' => false]);
            $log = $this->logAction('deactivate', $location);
            $this->clearApplicationCaches();

            $this->flashSuccessWithUndo(__('admin.location_updated_successfully'), $log);
            return redirect()->back();
        } catch (\Exception $e) {
            ErrorHelper::flashNotification($e->getMessage(), 'error');
            return redirect()->back();
        }
    }

    /**
     * Activate a location
     */
    public function activateLocation(Location $location)
    {
        try {
            $location->update(['is_active' => true]);
            $log = $this->logAction('activate', $location);
            $this->clearApplicationCaches();

            $this->flashSuccessWithUndo(__('admin.location_updated_successfully'), $log);
            return redirect()->back();
        } catch (\Exception $e) {
            ErrorHelper::flashNotification($e->getMessage(), 'error');
            return redirect()->back();
        }
    }

    /* =====================================================================
     |  BULK LOCATION ACTIONS
     * ===================================================================== */

    public function bulkLocations(Request $request)
    {
        return $this->runBulkAction($request, 'locations');
    }

    protected function bulkActions(string $resource): array
    {
        return [
            'activate' => __('admin.bulk_verb_activated'),
            'deactivate' => __('admin.bulk_verb_deactivated'),
            'delete' => __('admin.bulk_verb_deleted'),
        ];
    }

    protected function bulkQuery(string $resource): Builder
    {
        // withCount so the delete guard does not fire a query per row.
        return Location::query()->withCount('serviceProviders');
    }

    /**
     * @return true|string
     */
    protected function applyBulkAction(string $resource, string $action, $location)
    {
        switch ($action) {
            case 'activate':
                if ($location->is_active) {
                    return __('admin.bulk_reason_already_active');
                }
                $location->update(['is_active' => true]);
                $this->logAction('activate', $location);
                break;

            case 'deactivate':
                if (!$location->is_active) {
                    return __('admin.bulk_reason_already_inactive');
                }
                $location->update(['is_active' => false]);
                $this->logAction('deactivate', $location);
                break;

            case 'delete':
                if ($location->is_active) {
                    return __('admin.bulk_reason_must_deactivate_first');
                }
                if ((int) ($location->service_providers_count ?? 0) > 0) {
                    return __('admin.bulk_reason_has_providers');
                }
                if ($location->image && Storage::disk('public')->exists($location->image)) {
                    Storage::disk('public')->delete($location->image);
                }
                $this->logAction('delete', $location, ['deleted' => $location->toArray()]);
                $location->delete();
                break;

            default:
                return __('admin.bulk_reason_failed');
        }

        $this->clearApplicationCaches();

        return true;
    }
}
