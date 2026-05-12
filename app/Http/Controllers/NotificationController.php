<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications for the provider.
     * Supports filtering by read/unread status and pagination.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Get filter from query string (?filter=all|unread|read)
        $filter = $request->query('filter', 'all');
        
        // Build query with pagination
        $query = AdminNotification::active()
            ->orderBy('created_at', 'desc');
        
        // Get all active notification IDs for this user
        $allActiveIds = AdminNotification::active()->pluck('id');
        
        // Get read notification IDs
        $readNotificationIds = $user->readAdminNotifications()
            ->whereIn('admin_notification_id', $allActiveIds)
            ->pluck('admin_notification_id')
            ->toArray();
        
        // Apply filter
        if ($filter === 'unread') {
            $query->whereNotIn('id', $readNotificationIds);
        } elseif ($filter === 'read') {
            $query->whereIn('id', $readNotificationIds);
        }
        
        // Paginate results (15 per page)
        $notifications = $query->paginate(15)->appends($request->query());
        
        // Calculate counts for filter badges
        $totalCount = AdminNotification::active()->count();
        $unreadCount = $totalCount - count($readNotificationIds);
        $readCount = count($readNotificationIds);

        return view('notifications', compact(
            'notifications',
            'readNotificationIds',
            'filter',
            'totalCount',
            'unreadCount',
            'readCount'
        ));
    }

    /**
     * Mark notification(s) as read for the current user.
     * Clears the notification cache after marking.
     */
    public function markAsRead(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $notificationId = $request->integer('notification_id');
        $activeNotificationIds = AdminNotification::active()->pluck('id');
        $markedIds = collect();

        if ($request->filled('notification_id') && $activeNotificationIds->contains($notificationId)) {
            // Mark individual as read
            $user->readAdminNotifications()->syncWithoutDetaching([
                $notificationId => ['read_at' => now()]
            ]);
            $markedIds = collect([$notificationId]);
        } elseif (!$request->filled('notification_id')) {
            // Mark all active ones as read
            $syncData = [];
            foreach ($activeNotificationIds as $id) {
                $syncData[$id] = ['read_at' => now()];
            }
            
            $user->readAdminNotifications()->syncWithoutDetaching($syncData);
            $markedIds = $activeNotificationIds;
        }

        // Clear the notification cache for this user
        Cache::forget("nav_notifications_{$user->id}");

        $unreadCount = AdminNotification::active()
            ->whereNotIn('id', $user->readAdminNotifications()->pluck('admin_notification_id'))
            ->count();

        return response()->json([
            'success' => true,
            'marked_ids' => $markedIds->values()->all(),
            'unread_count' => $unreadCount,
        ]);
    }
    
    /**
     * Get unread notification count for the current user.
     * Used for AJAX polling to update badge without full page refresh.
     */
    public function unreadCount()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['count' => 0]);
        }
        
        $readIds = $user->readAdminNotifications()->pluck('admin_notification_id');
        
        $count = AdminNotification::active()
            ->whereNotIn('id', $readIds)
            ->count();
            
        return response()->json(['count' => $count]);
    }
}
