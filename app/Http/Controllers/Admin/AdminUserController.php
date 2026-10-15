<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ErrorHelper;
use App\Models\User;
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
use Illuminate\Support\Facades\Schema;

class AdminUserController extends Controller
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
     * Display list of all users with search, role filter, status filter, and sorting
     */
    public function users(Request $request)
    {
        try {
            $allowedSortFields = ['name', 'email', 'role', 'is_active', 'created_at'];
            $sortField = in_array($request->get('sortField', 'created_at'), $allowedSortFields)
                ? $request->get('sortField', 'created_at') : 'created_at';
            $sortDirection = $request->get('sortDirection', 'desc') === 'asc' ? 'asc' : 'desc';

            // withCount feeds the "Activity" column in the users table.
            $query = User::with('serviceProvider')->withCount(['reviews', 'comments']);

            // Search filter
            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            // Role filter
            if ($role = $request->get('role')) {
                $query->where('role', $role);
            }

            // Status filter
            if ($status = $request->get('status')) {
                $hasActiveColumn = Schema::hasColumn('users', 'is_active');
                if ($hasActiveColumn) {
                    $query->where('is_active', $status === 'active');
                }
            }

            $users = $query->orderBy($sortField, $sortDirection)
                 ->paginate(20)
                 ->withQueryString();

            // Stats
            $hasActiveColumn = Schema::hasColumn('users', 'is_active');
            $stats = [
                'total' => User::count(),
                'active' => $hasActiveColumn ? User::where('is_active', true)->count() : User::count(),
                'inactive' => $hasActiveColumn ? User::where('is_active', false)->count() : 0,
                'clients' => User::where('role', 'client')->count(),
                'providers' => User::where('role', 'service_provider')->count(),
            ];

            return view('admin.users.index', compact('users', 'stats'));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->route('admin.dashboard');
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleUserStatus(User $user)
    {
        try {
            // Never let an admin lock themselves out of the panel.
            if ($user->id === Auth::id()) {
                ErrorHelper::flashNotification(
                    __('admin.cannot_deactivate_yourself'),
                    'error'
                );
                return redirect()->back();
            }

            // Prevent deactivating the last remaining active admin.
            // (Guard applies when the user is currently ACTIVE, i.e. about to be turned off.)
            if ($user->isAdmin() && $user->is_active) {
                $otherActiveAdmins = User::where('role', 'admin')
                    ->where('is_active', true)
                    ->where('id', '!=', $user->id)
                    ->count();

                if ($otherActiveAdmins === 0) {
                    ErrorHelper::flashNotification(
                        __('admin.cannot_deactivate_last_admin'),
                        'error'
                    );
                    return redirect()->back();
                }
            }

            $newStatus = !$user->is_active;
            $user->update(['is_active' => $newStatus]);

            $this->logAction($newStatus ? 'activate' : 'deactivate', $user);
            $this->clearApplicationCaches();

            // Log the action
            Log::info('User status toggled by admin', [
                'user_id' => $user->id,
                'email' => $user->email,
                'new_status' => $newStatus,
                'admin_id' => Auth::id(),
            ]);

            $message = $newStatus 
                ? __('admin.user_activated_successfully') 
                : __('admin.user_deactivated_successfully');

            ErrorHelper::flashNotification($message, 'success');
            return redirect()->back();
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Move a user to the trash bin (reversible soft delete).
     *
     * Nothing is destroyed here: the account is deactivated and soft deleted so
     * it disappears from the public site while staying fully restorable from
     * admin/users/trash. Irreversible cleanup lives in forceDeleteUser().
     */
    public function deleteUser(User $user)
    {
        try {
            if ($guard = $this->guardUserRemoval($user)) {
                return $guard;
            }

            return DB::transaction(function () use ($user) {
                $userName = $user->name;

                // Public provider listings filter on users.is_active and skip
                // trashed users, so deactivating + soft deleting is enough to
                // pull the profile off the site without destroying anything.
                $user->update(['is_active' => false]);

                $log = $this->logAction('delete', $user, [
                    'trashed_user_email' => $user->email,
                    'trashed_user_name' => $userName,
                ]);

                $user->delete(); // soft delete

                $this->clearApplicationCaches();

                Log::info('User moved to trash by admin', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'admin_id' => Auth::id(),
                ]);

                $this->flashSuccessWithUndo(
                    __('admin.user_moved_to_trash', ['name' => $userName]),
                    $log
                );

                return redirect()->back();
            });
        } catch (\Exception $e) {
            Log::error('Moving user to trash failed: ' . $e->getMessage());
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Shared guard for both trashing and permanently deleting a user.
     * Returns a redirect when the removal must be blocked, null otherwise.
     */
    protected function guardUserRemoval(User $user)
    {
        if ($user->id === Auth::id()) {
            ErrorHelper::flashNotification(__('admin.cannot_delete_yourself'), 'error');
            return redirect()->back();
        }

        if ($user->isAdmin()) {
            $otherAdmins = User::where('role', 'admin')
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherAdmins === 0) {
                ErrorHelper::flashNotification(__('admin.cannot_delete_last_admin'), 'error');
                return redirect()->back();
            }
        }

        return null;
    }

    /**
     * Display soft deleted users
     */
    public function usersTrash()
    {
        $users = User::onlyTrashed()
            ->latest('deleted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.trash', compact('users'));
    }

    /**
     * Edit user data and role
     */
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user data and role
     */
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,client,service_provider',
            'is_active' => 'nullable|boolean',
        ]);

        $isSelf = $user->id === Auth::id();
        $newRole = $validated['role'];
        $newStatus = $request->boolean('is_active');

        // You may never change your own role or status from this form; the view
        // locks both fields, this is the server-side counterpart.
        if ($isSelf) {
            $newRole = $user->role;
            $newStatus = $user->is_active;
        }

        // Demoting or deactivating the last remaining active admin would lock
        // everyone out of the panel.
        $losesAdmin = $user->isAdmin() && ($newRole !== 'admin' || $newStatus === false);
        if ($losesAdmin) {
            $otherActiveAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins === 0) {
                ErrorHelper::flashNotification(__('admin.cannot_deactivate_last_admin'), 'error');
                return redirect()->back()->withInput();
            }
        }

        $oldValues = $user->getOriginal();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $newRole,
            'is_active' => $newStatus,
        ]);

        $this->logUpdate($user, $oldValues);
        $this->clearApplicationCaches();

        ErrorHelper::flashNotification(__('admin.user_updated_successfully'), 'success');
        return redirect()->route('admin.users');
    }

    /**
     * Restore a soft deleted user
     */
    public function restoreUser($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);

            DB::transaction(function () use ($user) {
                $user->restore();
                $user->update(['is_active' => true]);

                $this->logAction('restore', $user);
            });

            $this->clearApplicationCaches();

            ErrorHelper::flashNotification(__('admin.user_restored_successfully'), 'success');
            return redirect()->back();
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Permanently delete a user and every piece of data they own.
     *
     * This is the irreversible half of the trash workflow and is only reachable
     * from admin/users/trash. Files on disk are removed too, so there is no undo.
     */
    public function forceDeleteUser($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);

            if ($guard = $this->guardUserRemoval($user)) {
                return $guard;
            }

            DB::transaction(function () use ($user) {
                $userName = $user->name;
                $userEmail = $user->email;

                // 1. Service provider profile, media and uploaded documents.
                // (ServiceProvider does not use SoftDeletes, so no withTrashed here.)
                $provider = $user->serviceProvider;

                if ($provider) {
                    if (method_exists($provider, 'clearMediaCollection')) {
                        $provider->clearMediaCollection('gallery');
                    }

                    foreach ([$provider->profile_image, $provider->business_license] as $file) {
                        if ($file && Storage::disk('public')->exists($file)) {
                            Storage::disk('public')->delete($file);
                        }
                    }

                    $provider->reviews()->delete();
                    $provider->endorsements()->delete();
                    $provider->delete();
                }

                // 2. Content authored by the user.
                $user->reviews()->delete();
                $user->comments()->withTrashed()->forceDelete();
                $user->endorsements()->delete();
                $user->bookings()->delete();

                // 3. Pivot tables.
                $user->savedProviders()->detach();
                $user->readAdminNotifications()->detach();

                // 4. Audit trail (kept even though the row disappears).
                $this->logAction('permanent_delete', $user, [
                    'deleted_user_email' => $userEmail,
                    'deleted_user_name' => $userName,
                    'data_cleaned' => ['provider', 'media', 'reviews', 'comments', 'endorsements', 'bookings', 'bookmarks'],
                ]);

                $user->forceDelete();

                Log::warning('User permanently deleted by admin', [
                    'deleted_user_email' => $userEmail,
                    'admin_id' => Auth::id(),
                ]);
            });

            $this->clearApplicationCaches();

            ErrorHelper::flashNotification(__('admin.user_permanently_deleted'), 'success');
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('Permanent user deletion failed: ' . $e->getMessage());
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /* =====================================================================
     |  BULK USER ACTIONS
     * ===================================================================== */

    public function bulkUsers(Request $request)
    {
        return $this->runBulkAction($request, 'users');
    }

    protected function bulkActions(string $resource): array
    {
        return [
            'activate' => __('admin.bulk_verb_activated'),
            'deactivate' => __('admin.bulk_verb_deactivated'),
            'trash' => __('admin.bulk_verb_trashed'),
        ];
    }

    protected function bulkQuery(string $resource): Builder
    {
        return User::query();
    }

    /**
     * Bulk user actions run through the SAME protections as the single-item
     * routes: you can never act on yourself, and you can never remove the last
     * active admin — the batch just reports those rows as skipped.
     *
     * @return true|string
     */
    protected function applyBulkAction(string $resource, string $action, $user)
    {
        if ($user->id === Auth::id()) {
            return __('admin.bulk_reason_self');
        }

        return match ($action) {
            'activate' => $this->bulkActivateUser($user),
            'deactivate' => $this->bulkDeactivateUser($user),
            'trash' => $this->bulkTrashUser($user),
            default => __('admin.bulk_reason_failed'),
        };
    }

    private function bulkActivateUser(User $user)
    {
        if ($user->is_active) {
            return __('admin.bulk_reason_already_active');
        }

        $user->update(['is_active' => true]);
        $this->logAction('activate', $user);

        return true;
    }

    private function bulkDeactivateUser(User $user)
    {
        if (!$user->is_active) {
            return __('admin.bulk_reason_already_inactive');
        }

        if ($this->wouldRemoveLastActiveAdmin($user)) {
            return __('admin.bulk_reason_last_admin');
        }

        $user->update(['is_active' => false]);
        $this->logAction('deactivate', $user);

        return true;
    }

    private function bulkTrashUser(User $user)
    {
        if ($user->trashed()) {
            return __('admin.bulk_reason_already_trashed');
        }

        if ($user->isAdmin() && User::where('role', 'admin')->where('id', '!=', $user->id)->count() === 0) {
            return __('admin.bulk_reason_last_admin');
        }

        // Mirrors deleteUser(): deactivate + soft delete, nothing destroyed.
        $user->update(['is_active' => false]);
        $this->logAction('delete', $user, [
            'trashed_user_email' => $user->email,
            'trashed_user_name' => $user->name,
        ]);
        $user->delete();

        return true;
    }

    /**
     * Would deactivating this user leave the platform with no active admin?
     */
    private function wouldRemoveLastActiveAdmin(User $user): bool
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return User::where('role', 'admin')
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->count() === 0;
    }
}
