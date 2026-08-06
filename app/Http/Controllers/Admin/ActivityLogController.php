<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display list of admin actions, with filters.
     */
    public function index(Request $request)
    {
        $query = AdminLog::with('admin');

        // Undone entries are hidden unless explicitly requested, so the default
        // view only shows actions that are still in effect.
        if (!$request->boolean('include_undone')) {
            $query->where('is_undone', false);
        }

        if ($request->filled('action')) {
            $query->ofAction($request->input('action'));
        }

        if ($request->filled('model_type')) {
            $query->ofModelType($request->input('model_type'));
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->input('admin_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('model_name', 'like', "%{$search}%");
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $filterOptions = [
            'actions' => AdminLog::query()->distinct()->orderBy('action')->pluck('action'),
            'models' => AdminLog::query()
                ->whereNotNull('model_type')
                ->distinct()
                ->orderBy('model_type')
                ->pluck('model_type'),
            'admins' => User::where('role', 'admin')->orderBy('name')->pluck('name', 'id'),
        ];

        return view('admin.activity_logs.index', compact('logs', 'filterOptions'));
    }
}
