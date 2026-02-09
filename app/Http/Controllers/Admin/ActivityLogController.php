<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display list of admin actions.
     */
    public function index()
    {
        $logs = AdminLog::with('admin')
            ->where('is_undone', false)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.activity_logs.index', compact('logs'));
    }
}
