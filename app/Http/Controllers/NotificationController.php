<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications for the provider.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $notifications = AdminNotification::active()
            ->orderBy('created_at', 'desc')
            ->get();
            
        $readNotificationIds = $user->readAdminNotifications()
            ->whereIn('admin_notification_id', $notifications->pluck('id'))
            ->pluck('admin_notification_id')
            ->toArray();

        return view('notifications', compact('notifications', 'readNotificationIds'));
    }

    /**
     * Mark all active notifications as read for the current user.
     */
    // @change 2026-04-14 TASK-5 | Tightened provider notification read-state updates and returned unread metadata | The dropdown badge must clear reliably after opening or clicking a notification | risk:LOW
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

        $unreadCount = AdminNotification::active()
            ->whereNotIn('id', $user->readAdminNotifications()->pluck('admin_notification_id'))
            ->count();

        return response()->json([
            'success' => true,
            'marked_ids' => $markedIds->values()->all(),
            'unread_count' => $unreadCount,
        ]);
    }
}
