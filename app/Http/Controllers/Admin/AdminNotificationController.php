<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ErrorHelper;

class AdminNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of notifications.
     */
    public function index()
    {
        $notifications = AdminNotification::with('admin')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.notifications.index', compact('notifications'));
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
            ErrorHelper::flashNotification('Notification deleted successfully.', 'success');
            return redirect()->route('admin.notifications.index');
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }
}
