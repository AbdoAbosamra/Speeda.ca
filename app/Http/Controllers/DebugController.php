<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\Storage;

class DebugController extends Controller
{
    /**
     * Show diagnostic info for authenticated user.
     */
    public function status(Request $request)
    {
        $user = $request->user();

        // Migration status
        try {
            Artisan::call('migrate:status', ['--force' => true]);
            $migrateOutput = Artisan::output();
        } catch (\Exception $e) {
            $migrateOutput = $e->getMessage();
        }

        // Check admin route presence
        $hasAdminRoute = RouteFacade::has('admin.dashboard');

        // Storage link check (public/storage exists)
        $publicStorageLinked = file_exists(public_path('storage')) || is_link(public_path('storage'));

        // Location counts
        $locationsCount = 0;
        $activeCount = 0;
        try {
            $locationsCount = \App\Models\Location::count();
            $activeCount = \App\Models\Location::where('is_active', true)->count();
        } catch (\Exception $e) {
            // ignore
        }

        return view('debug.status', compact(
            'user', 'migrateOutput', 'hasAdminRoute', 'publicStorageLinked', 'locationsCount', 'activeCount'
        ));
    }
}
