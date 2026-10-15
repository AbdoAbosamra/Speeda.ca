<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ErrorHelper;
use App\Traits\ClearsAdminCaches;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class AdminMaintenanceController extends Controller
{
    use ClearsAdminCaches;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Clear common caches (views, routes, config, app cache)
     */
    public function clearCache(Request $request)
    {
        try {
            $this->flushAllCaches();
            ErrorHelper::flashNotification(__('admin.cache_cleared'), 'success');
            return redirect()->back();
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Full cache flush, only triggered by the explicit "Clear Caches"
     * maintenance button — never as a side effect of a CRUD write.
     */
    protected function flushAllCaches(): void
    {
        $this->clearApplicationCaches();

        try {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
        } catch (\Exception $e) {
            Log::warning('Failed to flush application caches', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
