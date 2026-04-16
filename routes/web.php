<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EndorsementController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderAnalyticsExportController;
use App\Http\Controllers\ProviderDashboardController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceProviderAnalyticsController;
use App\Http\Controllers\ServiceProviderController;
use App\Http\Controllers\Admin\AdminCommentController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\VisitorAnalyticsController;
use App\Http\Controllers\DebugController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Include authentication routes
require __DIR__ . '/auth.php';

// ==================== LOCALE ROUTES ====================
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
Route::get('/current-locale', [LocaleController::class, 'getCurrentLocale'])->name('locale.current');

// ==================== PUBLIC ROUTES ====================
Route::view('/', 'home')->name('home');

// Test translation route (internal diagnostics only)
Route::middleware(['auth', 'admin'])->get('/test-translations', function () {
    return response()->json([
        'roadside_en' => __('categories.roadside_assistance_24_7'),
        'accounting_en' => __('categories.accounting_bookkeeping_tax_preparation'),
        'roadside_ar' => trans('categories.roadside_assistance_24_7', [], 'ar'),
        'accounting_ar' => trans('categories.accounting_bookkeeping_tax_preparation', [], 'ar'),
    ]);
});

// === DIAGNOSTIC ROUTE (Internal debugging only) ===
Route::middleware(['auth', 'admin'])->get('/diagnostic', function () {
    $totalSP = \App\Models\ServiceProvider::count();
    $spWithImages = \App\Models\ServiceProvider::whereNotNull('profile_image')
        ->where('profile_image', '!=', '')
        ->count();

    $serviceProviders = \App\Models\ServiceProvider::limit(3)->get();

    $storageDir = storage_path('app/public/profile-images');
    $storageFiles = [];
    if (is_dir($storageDir)) {
        $files = array_diff(scandir($storageDir), ['.', '..', '.gitignore']);
        $storageFiles = array_values($files);
    }
    $storageCount = count($storageFiles);

    return view('diagnostic', compact(
        'totalSP',
        'spWithImages',
        'serviceProviders',
        'storageFiles',
        'storageCount'
    ));
})->name('diagnostic');

// Static pages
Route::get('/locations', [LocationController::class, 'index'])->name('location');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::view('/privacy-policy', 'Static.PrivacyPolicy')->name('privacy-policy');
Route::view('/terms-of-service', 'Static.terms-of-service')->name('terms-of-service');
Route::view('/help-center', 'Static.help-center')->name('help-center');
Route::view('/legal-affairs', 'Static.legal-affairs')->name('legal-affairs');
Route::view('/about-us', 'about-us')->name('about-us');

// ==================== SERVICE PROVIDERS - PUBLIC ====================
Route::get('/service-providers', [ServiceProviderController::class, 'index'])
    ->name('service-providers.index');

// Public profile view
Route::get('/service-providers/{serviceProvider}', [ServiceProviderController::class, 'show'])
    ->name('service-providers.show');

// Track contact reveal (no auth required, uses session)
Route::post('/service-providers/{serviceProvider}/reveal-contact', [ServiceProviderController::class, 'revealContact'])
    ->middleware('throttle:5,1')
    ->name('service-providers.reveal-contact');

// Track provider analytics click events (same-origin relative endpoint used by profile page)
Route::post('/service-providers/{serviceProvider}/analytics/click', [ServiceProviderAnalyticsController::class, 'trackClick'])
    ->middleware('throttle:20,1')
    ->name('service-providers.analytics.click');

// ==================== AUTHENTICATED ROUTES ====================
Route::middleware(['auth'])->get('/dashboard', function () {
    // Redirect admin to admin dashboard
    /** @var \App\Models\User $user */
    $user = Auth::user();
    if ($user && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('service-providers.index');
})->name('dashboard');

// Simple authenticated diagnostics for debugging roles, migrations, storage, locations
Route::middleware(['auth'])->get('/debug-status', [DebugController::class, 'status'])->name('debug.status');

// ==================== USER PROFILE ROUTES ====================
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->prefix('service-providers')->group(function () {
    // Provider analytics dashboard
    Route::get('/dashboard', [ProviderDashboardController::class, 'index'])
        ->name('service-providers.dashboard');

    Route::get('/analytics/export-pdf', [ProviderAnalyticsExportController::class, 'exportPdf'])
        ->name('service-providers.analytics.export-pdf');

    // View own profile - redirect to show page (with edit section for owner)
    Route::get('/profile', function () {
        $provider = Auth::user()->serviceProvider;
        if (!$provider) {
            return redirect()->route('dashboard')->with('error', __('service_provider.no_profile_found'));
        }
        return redirect()->route('service-providers.show', $provider);
    })->name('service-providers.profile');

    // Edit profile (legacy - redirects to show)
    Route::get('/{serviceProvider}/edit', function ($serviceProvider) {
        return redirect()->route('service-providers.show', $serviceProvider);
    })->name('service-providers.edit');

    // Update profile - Rate limited to prevent spam (max 10 requests per minute)
    Route::put('/profile/{serviceProvider}', [ServiceProviderController::class, 'updateProfile'])
        ->middleware('throttle:10,1')
        ->name('service-providers.profile.update');

    Route::delete('/profile/{serviceProvider}/gallery/{media}', [ServiceProviderController::class, 'deleteGalleryImage'])
        ->middleware('throttle:20,1')
        ->name('service-providers.profile.gallery.delete');

    Route::patch('/profile/{serviceProvider}/gallery/{media}', [ServiceProviderController::class, 'replaceGalleryImage'])
        ->middleware('throttle:20,1')
        ->name('service-providers.profile.gallery.replace');

    // ── AJAX Gallery routes (GalleryController — JSON responses) ──
    Route::post('/profile/{serviceProvider}/gallery', [GalleryController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('provider.gallery.store');

    Route::post('/profile/{serviceProvider}/gallery/{media}/replace', [GalleryController::class, 'update'])
        ->middleware('throttle:20,1')
        ->name('provider.gallery.update');

    Route::delete('/profile/{serviceProvider}/gallery/{media}/ajax', [GalleryController::class, 'destroy'])
        ->middleware('throttle:20,1')
        ->name('provider.gallery.destroy');

    // Handle profile image upload
    Route::post('/profile/image-upload', [ServiceProviderController::class, 'uploadProfileImage'])
        ->name('service-providers.profile.image-upload');

    // Dismiss engagement popup
    Route::post('/profile/popup-dismissed', [ServiceProviderController::class, 'dismissEngagementPopup'])
        ->name('service-providers.popup-dismissed');
});

// ==================== REVIEWS ROUTES (Client) ====================
// Public routes - see active reviews
Route::get('/service-providers/{serviceProvider}/reviews', [ReviewController::class, 'index'])
    ->name('reviews.index');
Route::get('/reviews/{review}', [ReviewController::class, 'show'])
    ->name('reviews.show');

// Authenticated client routes - create/edit/delete reviews
Route::get('/reviews/create/{serviceProvider}', [ReviewController::class, 'create'])
    ->name('reviews.create');
Route::middleware(['auth'])->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

// ==================== RATINGS ROUTES ====================
Route::middleware(['auth'])->group(function () {
    Route::post('/service-providers/{serviceProvider}/rate', [RatingController::class, 'store'])
        ->name('ratings.store');
    Route::get('/service-providers/{serviceProvider}/my-rating', [RatingController::class, 'getUserRating'])
        ->name('ratings.user');
});

// ==================== ENDORSEMENTS ROUTES ====================
Route::middleware(['auth'])->group(function () {
    Route::post('/service-providers/{serviceProvider}/endorse', [EndorsementController::class, 'toggle'])
        ->name('endorsements.toggle');
});

// ==================== COMMENTS ROUTES ====================
// Public routes - see active comments
Route::get('/comments', [CommentController::class, 'index'])->name('comments.index');

// Authenticated routes - create/edit/delete/flag comments
Route::middleware(['auth'])->prefix('comments')->name('comments.')->group(function () {
    Route::get('/create', [CommentController::class, 'create'])->name('create');
    Route::post('/', [CommentController::class, 'store'])->name('store');
    Route::get('/{comment}/edit', [CommentController::class, 'edit'])->name('edit');
    Route::put('/{comment}', [CommentController::class, 'update'])->name('update');
    Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('destroy');
    Route::post('/{comment}/flag', [CommentController::class, 'flag'])->name('flag');
});

// ==================== ADMIN ROUTES ====================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Activity Logs
    Route::get('/activity-logs', [App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity_logs');

    // Undo Action
    Route::post('/undo/{log}', [App\Http\Controllers\Admin\UndoController::class, 'undo'])->name('undo');

    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Categories Management (using IDs, no slugs)
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::get('/categories/{category}/edit', [AdminController::class, 'editCategory'])->name('categories.edit');
    Route::patch('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminController::class, 'deleteCategory'])->name('categories.destroy');
    Route::patch('/categories/{category}/toggle', [AdminController::class, 'toggleCategoryStatus'])->name('categories.toggle');

    // Users Management (using IDs, no slugs)
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::patch('/users/{user}/toggle', [AdminController::class, 'toggleUserStatus'])->name('users.toggle');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');

    // Locations
    Route::get('/locations', [AdminController::class, 'locations'])->name('locations');
    Route::post('/locations', [AdminController::class, 'storeLocation'])->name('locations.store');
    Route::put('/locations/{location}', [AdminController::class, 'updateLocation'])->name('locations.update');
    Route::delete('/locations/{location}', [AdminController::class, 'deleteLocation'])->name('locations.delete');
    Route::patch('/locations/{location}/deactivate', [AdminController::class, 'deactivateLocation'])->name('locations.deactivate');
    Route::patch('/locations/{location}/activate', [AdminController::class, 'activateLocation'])->name('locations.activate');

    // Visitor Analytics (Read-only)
    Route::get('/visitors', [VisitorAnalyticsController::class, 'index'])->name('visitors');
    Route::get('/visitors/live-count', [VisitorAnalyticsController::class, 'getLiveCount'])->name('visitors.live-count');
    Route::get('/visitors/export', [VisitorAnalyticsController::class, 'export'])->name('visitors.export');

    // Utility - Clear caches to ensure admin changes reflect immediately
    Route::post('/clear-cache', [AdminController::class, 'clearCache'])->name('clear-cache');

    // Reviews Management (Admin approval workflow)
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews');
    Route::get('/reviews/{review}', [AdminReviewController::class, 'show'])->name('reviews.show');
    Route::post('/reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{review}/reject', [AdminReviewController::class, 'reject'])->name('reviews.reject');
    Route::post('/reviews/{review}/feature', [AdminReviewController::class, 'feature'])->name('reviews.feature');
    Route::post('/reviews/{review}/unfeature', [AdminReviewController::class, 'unfeature'])->name('reviews.unfeature');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'delete'])->name('reviews.delete');

    // Comments Management (Admin approval workflow)
    Route::get('/comments', [AdminCommentController::class, 'index'])->name('comments');
    Route::get('/comments/{comment}', [AdminCommentController::class, 'show'])->name('comments.show');
    Route::post('/comments/{comment}/approve', [AdminCommentController::class, 'approve'])->name('comments.approve');
    Route::post('/comments/{comment}/reject', [AdminCommentController::class, 'reject'])->name('comments.reject');
    Route::post('/comments/{comment}/flag', [AdminCommentController::class, 'flag'])->name('comments.flag');
    Route::post('/comments/{comment}/unflag', [AdminCommentController::class, 'unflag'])->name('comments.unflag');
    Route::delete('/comments/{comment}', [AdminCommentController::class, 'delete'])->name('comments.delete');
    Route::post('/comments/{comment}/restore', [AdminCommentController::class, 'restore'])->name('comments.restore');

    // Notifications Management
    Route::get('/notifications', [App\Http\Controllers\Admin\AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/create', [App\Http\Controllers\Admin\AdminNotificationController::class, 'create'])->name('notifications.create');
    Route::post('/notifications', [App\Http\Controllers\Admin\AdminNotificationController::class, 'store'])->name('notifications.store');
    Route::delete('/notifications/{notification}', [App\Http\Controllers\Admin\AdminNotificationController::class, 'destroy'])->name('notifications.destroy');
});

// Service Provider Notification Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/notifications/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
});


// ==================== CSRF TOKEN ====================
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf-token');
