<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ErrorHelper;
use Illuminate\Support\Facades\Cache;

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
        $query = AdminNotification::with('admin')
            ->orderBy('created_at', 'desc');
        
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
        ];

        return view('admin.notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Show the form for creating a new notification.
     */
    public function create()
    {
        return view('admin.notifications.create');
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
        ]);

        try {
            AdminNotification::create([
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
            
            // Clear all notification caches since a new notification was added
            $this->clearAllNotificationCaches();

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
            $notification->delete();
            
            // Clear all notification caches
            $this->clearAllNotificationCaches();
            
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
        $providerIds = \App\Models\User::whereHas('serviceProvider')->pluck('id');
        
        foreach ($providerIds as $userId) {
            Cache::forget("nav_notifications_{$userId}");
        }
    }
}
