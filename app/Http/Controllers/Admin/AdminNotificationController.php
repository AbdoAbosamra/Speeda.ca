<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ErrorHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of notifications with search and filters.
     */
    public function index(Request $request)
    {
        $targetingEnabled = AdminNotification::supportsProviderTargeting();

        $query = AdminNotification::with('admin')
            ->orderBy('created_at', 'desc');

        if ($targetingEnabled) {
            $query->with(['targetServiceProviders.user'])
                ->withCount('targetServiceProviders');
        }
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title_en', 'like', "%{$search}%")
                  ->orWhere('title_ar', 'like', "%{$search}%")
                  ->orWhere('title_fr', 'like', "%{$search}%")
                  ->orWhere('message_en', 'like', "%{$search}%")
                  ->orWhere('message_ar', 'like', "%{$search}%")
                  ->orWhere('message_fr', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('expires_at', '>', now());
            } elseif ($request->input('status') === 'expired') {
                $query->where('expires_at', '<=', now());
            }
        }
        
        $notifications = $query->paginate(15)->withQueryString();
        
        // Stats for dashboard cards
        $stats = [
            'total' => AdminNotification::count(),
            'active' => AdminNotification::active()->count(),
            'expired' => AdminNotification::where('expires_at', '<=', now())->count(),
            'targeted' => $targetingEnabled ? AdminNotification::has('targetServiceProviders')->count() : 0,
        ];

        return view('admin.notifications.index', compact('notifications', 'stats', 'targetingEnabled'));
    }

    /**
     * Show the form for creating a new notification.
     */
    public function create()
    {
        $serviceProviders = ServiceProvider::query()
            ->with('user')
            ->whereHas('user', function ($query) {
                $query->where('role', 'service_provider')
                    ->where('is_active', true);
            })
            ->orderBy('company_name')
            ->get();

        $targetingEnabled = AdminNotification::supportsProviderTargeting();

        return view('admin.notifications.create', compact('serviceProviders', 'targetingEnabled'));
    }

    /**
     * Store a newly created notification.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title_ar' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'title_fr' => 'required|string|max:255',
            'message_ar' => 'required|string',
            'message_en' => 'required|string',
            'message_fr' => 'required|string',
            'target_mode' => 'required|in:all,selected',
            'service_provider_ids' => 'required_if:target_mode,selected|array',
            'service_provider_ids.*' => 'integer|exists:service_providers,id',
        ]);

        if ($validated['target_mode'] === 'selected' && !AdminNotification::supportsProviderTargeting()) {
            throw ValidationException::withMessages([
                'target_mode' => 'Targeted notifications are not available until the notification targeting migration has run.',
            ]);
        }

        $targetProviderIds = collect($validated['service_provider_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($validated['target_mode'] === 'selected') {
            $activeTargetProviderIds = ServiceProvider::query()
                ->whereIn('id', $targetProviderIds->all())
                ->whereHas('user', function ($query) {
                    $query->where('role', 'service_provider')
                        ->where('is_active', true);
                })
                ->pluck('id');

            if ($activeTargetProviderIds->count() !== $targetProviderIds->count()) {
                throw ValidationException::withMessages([
                    'service_provider_ids' => 'Selected recipients must be active service providers.',
                ]);
            }

            $targetProviderIds = $activeTargetProviderIds->values();
        }

        try {
            $notification = DB::transaction(function () use ($validated, $targetProviderIds) {
                $notification = AdminNotification::create([
                    'title_ar' => $validated['title_ar'],
                    'title_en' => $validated['title_en'],
                    'title_fr' => $validated['title_fr'],
                    'message_ar' => $validated['message_ar'],
                    'message_en' => $validated['message_en'],
                    'message_fr' => $validated['message_fr'],
                    'target_type' => 'provider_only',
                    'created_by' => Auth::id(),
                    'expires_at' => now()->addDays(30),
                ]);

                if ($validated['target_mode'] === 'selected') {
                    $notification->targetServiceProviders()->sync($targetProviderIds->all());
                }

                return $notification;
            });
            
            $this->clearNotificationCachesForNotification($notification);

            ErrorHelper::flashNotification('Notification sent successfully.', 'success');
            return redirect()->route('admin.notifications.index');
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified notification.
     */
    public function destroy(AdminNotification $notification)
    {
        try {
            $targetProviderIds = AdminNotification::supportsProviderTargeting()
                ? $notification->targetServiceProviders()->pluck('service_providers.id')
                : collect();

            $notification->delete();
            
            $this->clearNotificationCaches($targetProviderIds->all());
            
            ErrorHelper::flashNotification('Notification deleted successfully.', 'success');
            return redirect()->route('admin.notifications.index');
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }
    
    /**
     * Clear all notification caches for all users.
     */
    private function clearAllNotificationCaches(): void
    {
        // Get all service provider user IDs and clear their caches
        // This ensures the dropdown shows updated notifications
        $providerIds = User::whereHas('serviceProvider')->pluck('id');
        
        foreach ($providerIds as $userId) {
            Cache::forget("nav_notifications_{$userId}");
        }
    }

    /**
     * Clear notification caches for a broadcast or targeted notification.
     */
    private function clearNotificationCachesForNotification(AdminNotification $notification): void
    {
        if (!AdminNotification::supportsProviderTargeting()) {
            $this->clearAllNotificationCaches();
            return;
        }

        $targetProviderIds = $notification->targetServiceProviders()
            ->pluck('service_providers.id')
            ->all();

        $this->clearNotificationCaches($targetProviderIds);
    }

    /**
     * Clear selected provider user caches, or all provider caches for broadcast notifications.
     */
    private function clearNotificationCaches(array $serviceProviderIds = []): void
    {
        if (empty($serviceProviderIds)) {
            $this->clearAllNotificationCaches();
            return;
        }

        $userIds = ServiceProvider::whereIn('id', $serviceProviderIds)
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            Cache::forget("nav_notifications_{$userId}");
        }
    }
}
