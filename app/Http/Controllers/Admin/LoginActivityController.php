<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Admin view of service-provider login activity and live presence.
 * Backed by the users table columns: login_count, last_login_at,
 * last_login_ip and the last_seen_at heartbeat.
 */
class LoginActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $onlineThreshold = now()->subMinutes(User::ONLINE_THRESHOLD_MINUTES);

        $query = User::query()
            ->where('role', 'service_provider')
            ->with('serviceProvider');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->input('status') === 'online') {
            $query->where('last_seen_at', '>', $onlineThreshold);
        } elseif ($request->input('status') === 'never') {
            $query->whereNull('last_login_at');
        }

        $providers = $query
            ->orderByRaw('last_seen_at IS NULL, last_seen_at DESC')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => User::where('role', 'service_provider')->count(),
            'online' => User::where('role', 'service_provider')
                ->where('last_seen_at', '>', $onlineThreshold)->count(),
            'today' => User::where('role', 'service_provider')
                ->whereDate('last_login_at', today())->count(),
            'never' => User::where('role', 'service_provider')
                ->whereNull('last_login_at')->count(),
        ];

        return view('admin.login_activity.index', compact('providers', 'stats'));
    }
}
