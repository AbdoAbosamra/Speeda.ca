<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\EndorsementController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderAnalyticsExportController;
use App\Http\Controllers\ProviderDashboardController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceProviderAnalyticsController;
use App\Http\Controllers\ServiceProviderController;
use App\Http\Controllers\Admin\AdminCommentController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminLocationController;
use App\Http\Controllers\Admin\AdminMaintenanceController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\LegalPageController as AdminLegalPageController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\VisitorAnalyticsController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Include authentication routes
require __DIR__ . '/auth.php';

// ==================== LOCALE ROUTES ====================
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
Route::get('/current-locale', [LocaleController::class, 'getCurrentLocale'])->name('locale.current');

// ==================== PUBLIC ROUTES ====================
Route::get('/', [HomeController::class, 'index'])->name('home');

// Static pages
Route::get('/locations', [LocationController::class, 'index'])->name('location');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/{post:slug}', [BlogController::class, 'show'])->name('blogs.show');
Route::get('/privacy-policy', [LegalPageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-of-service', [LegalPageController::class, 'termsOfService'])->name('terms-of-service');
Route::get('/legal/{slug}', [LegalPageController::class, 'show'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('legal-pages.show');
Route::view('/help-center', 'Static.help-center')->name('help-center');
Route::view('/legal-affairs', 'Static.legal-affairs')->name('legal-affairs');
Route::view('/about-us', 'about-us')->name('about-us');

// ==================== LOCAL OFFERS - PUBLIC ====================
Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');
Route::post('/offers/track', [OfferController::class, 'track'])
    ->middleware('throttle:60,1')
    ->name('offers.track');
// Favorites belong to client accounts.
Route::get('/my-favorites', [\App\Http\Controllers\OfferEngagementController::class, 'favorites'])
    ->middleware('auth')
    ->name('offers.favorites');
/* Temporarily disabled: offer likes and comments. Keep the routes for later.
Route::post('/offers/{slug}/like', [\App\Http\Controllers\OfferEngagementController::class, 'like'])
    ->middleware('throttle:30,1')
    ->where('slug', '[a-z0-9-]+')
    ->name('offers.like');
Route::post('/offers/{slug}/comments', [\App\Http\Controllers\OfferEngagementController::class, 'comment'])
    ->middleware('throttle:offer-comments')
    ->where('slug', '[a-z0-9-]+')
    ->name('offers.comment');
*/
Route::post('/offers/{slug}/favorite', [\App\Http\Controllers\OfferEngagementController::class, 'favorite'])
    ->middleware('throttle:30,1')
    ->where('slug', '[a-z0-9-]+')
    ->name('offers.favorite');
Route::get('/offers/{slug}', [OfferController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('offers.show');
// "Get this offer on WhatsApp": counts the click and opens the chat.
Route::get('/offers/{slug}/whatsapp', [OfferController::class, 'go'])
    ->middleware('throttle:30,1')
    ->where('slug', '[a-z0-9-]+')
    ->name('offers.go');

// ==================== SERVICE PROVIDERS - PUBLIC ====================
Route::get('/service-providers', [ServiceProviderController::class, 'index'])
    ->name('service-providers.index');

// Public profile view
Route::get('/service-providers/{serviceProvider}', [ServiceProviderController::class, 'show'])
    ->whereNumber('serviceProvider')
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


// ==================== USER PROFILE ROUTES ====================
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->middleware('throttle:20,1')->name('profile.update');
    // Asks for the account password: limited so it cannot be guessed.
    Route::delete('/profile', [ProfileController::class, 'destroy'])->middleware('throttle:6,1')->name('profile.destroy');
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

    /* Provider gallery uploads are closed; keep existing images and deletion available.
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
    */

    Route::delete('/profile/{serviceProvider}/gallery/{media}/ajax', [GalleryController::class, 'destroy'])
        ->middleware('throttle:20,1')
        ->name('provider.gallery.destroy');

    // Handle profile image upload
    Route::post('/profile/image-upload', [ServiceProviderController::class, 'uploadProfileImage'])
        ->middleware('throttle:20,1')
        ->name('service-providers.profile.image-upload');

    // Dismiss engagement popup
    Route::post('/profile/popup-dismissed', [ServiceProviderController::class, 'dismissEngagementPopup'])
        ->name('service-providers.popup-dismissed');

    // Dismiss completion popup (session-based)
    Route::post('/profile/completion-popup-dismiss', [ServiceProviderController::class, 'dismissCompletionPopup'])
        ->name('service-providers.dismiss-completion-popup');
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
Route::middleware(['auth', 'throttle:20,1'])->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

// ==================== RATINGS ROUTES ====================
Route::middleware(['auth', 'throttle:30,1'])->group(function () {
    Route::post('/service-providers/{serviceProvider}/rate', [RatingController::class, 'store'])
        ->name('ratings.store');
    Route::get('/service-providers/{serviceProvider}/my-rating', [RatingController::class, 'getUserRating'])
        ->name('ratings.user');
});

// ==================== ENDORSEMENTS ROUTES ====================
Route::middleware(['auth', 'throttle:30,1'])->group(function () {
    Route::post('/service-providers/{serviceProvider}/endorse', [EndorsementController::class, 'toggle'])
        ->name('endorsements.toggle');
});

// ==================== COMMENTS ROUTES ====================
// Public routes - see active comments
Route::get('/comments', [CommentController::class, 'index'])->name('comments.index');

// Authenticated routes - create/edit/delete/flag comments
Route::middleware(['auth', 'throttle:30,1'])->prefix('comments')->name('comments.')->group(function () {
    Route::get('/create', [CommentController::class, 'create'])->name('create');
    Route::post('/', [CommentController::class, 'store'])->name('store');
    Route::get('/{comment}/edit', [CommentController::class, 'edit'])->name('edit');
    Route::put('/{comment}', [CommentController::class, 'update'])->name('update');
    Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('destroy');
    Route::post('/{comment}/flag', [CommentController::class, 'flag'])->name('flag');
});

// ==================== EMAIL UNSUBSCRIBE ====================
// Reached from the footer of any non-transactional Speeda email. No auth on
// purpose: requiring a login to unsubscribe is how opt-out links quietly stop
// working, and mailbox providers expect it to work straight from the inbox.
// The signature is what proves the link belongs to that specific user.
//
// The URIs are unchanged from when this covered broadcasts only, so opt-out
// links inside already-delivered mail keep working.
Route::middleware('signed')->group(function () {
    Route::get('/email/unsubscribe/{user}', [\App\Http\Controllers\EmailUnsubscribeController::class, 'show'])
        ->name('email.unsubscribe');
    Route::post('/email/unsubscribe/{user}', [\App\Http\Controllers\EmailUnsubscribeController::class, 'store'])
        ->name('email.unsubscribe.confirm');
    Route::post('/email/resubscribe/{user}', [\App\Http\Controllers\EmailUnsubscribeController::class, 'resubscribe'])
        ->name('email.resubscribe');
});

// ==================== ADMIN ROUTES ====================
// throttle:120,1 is a safety net against runaway scripts / credential-stuffed
// sessions hammering the panel; it is well above normal interactive use.
// admin.locale pins the panel to English; site language stays untouched for the
// public site. Database content (Arabic category names, bios, reviews) is not
// affected — only __() UI strings.
Route::middleware(['auth', 'admin', 'admin.locale', 'throttle:120,1'])->prefix('admin')->name('admin.')->group(function () {
    // Redirect /admin to /admin/dashboard
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    // Activity Logs
    Route::get('/activity-logs', [App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity_logs');

    // Undo Action
    Route::post('/undo/{log}', [App\Http\Controllers\Admin\UndoController::class, 'undo'])->name('undo');

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');

    // Blog CMS
    // Trash routes are declared before the resource so blog/posts/trash is not
    // swallowed by the blog/posts/{post} wildcard.
    Route::post('blog/posts/bulk', [BlogPostController::class, 'bulk'])->name('blog.posts.bulk');
    Route::get('blog/posts/trash', [BlogPostController::class, 'trash'])->name('blog.posts.trash');
    Route::post('blog/posts/{post}/restore', [BlogPostController::class, 'restore'])->name('blog.posts.restore');
    Route::resource('blog/posts', BlogPostController::class)
        ->except(['show'])
        ->names('blog.posts')
        ->parameters(['posts' => 'post']);

    // Legal Pages CMS
    Route::resource('legal-pages', AdminLegalPageController::class)
        ->except(['show'])
        ->names('legal-pages');

    // Categories Management (using IDs, no slugs)
    Route::get('/categories', [AdminCategoryController::class, 'categories'])->name('categories');
    Route::post('/categories/bulk', [AdminCategoryController::class, 'bulkCategories'])->name('categories.bulk');
    Route::post('/categories', [AdminCategoryController::class, 'storeCategory'])->name('categories.store');
    Route::get('/categories/{category}/edit', [AdminCategoryController::class, 'editCategory'])->name('categories.edit');
    Route::patch('/categories/{category}', [AdminCategoryController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'deleteCategory'])->name('categories.destroy');
    Route::patch('/categories/{category}/toggle', [AdminCategoryController::class, 'toggleCategoryStatus'])->name('categories.toggle');

    // Users Management
    Route::get('/users', [AdminUserController::class, 'users'])->name('users');
    Route::post('/users/bulk', [AdminUserController::class, 'bulkUsers'])->name('users.bulk');
    Route::get('/users/trash', [AdminUserController::class, 'usersTrash'])->name('users.trash');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'editUser'])->name('users.edit');
    Route::patch('/users/{user}', [AdminUserController::class, 'updateUser'])->name('users.update');
    Route::patch('/users/{user}/toggle', [AdminUserController::class, 'toggleUserStatus'])->name('users.toggle');
    Route::delete('/users/{user}', [AdminUserController::class, 'deleteUser'])->name('users.delete');
    Route::post('/users/{id}/restore', [AdminUserController::class, 'restoreUser'])->name('users.restore');
    Route::delete('/users/{id}/force', [AdminUserController::class, 'forceDeleteUser'])->name('users.force_delete');

    // Locations
    Route::get('/locations', [AdminLocationController::class, 'locations'])->name('locations');
    Route::post('/locations/bulk', [AdminLocationController::class, 'bulkLocations'])->name('locations.bulk');
    Route::post('/locations', [AdminLocationController::class, 'storeLocation'])->name('locations.store');
    Route::put('/locations/{location}', [AdminLocationController::class, 'updateLocation'])->name('locations.update');
    Route::delete('/locations/{location}', [AdminLocationController::class, 'deleteLocation'])->name('locations.delete');
    Route::patch('/locations/{location}/deactivate', [AdminLocationController::class, 'deactivateLocation'])->name('locations.deactivate');
    Route::patch('/locations/{location}/activate', [AdminLocationController::class, 'activateLocation'])->name('locations.activate');

    // Visitor Analytics (Read-only)
    Route::get('/visitors', [VisitorAnalyticsController::class, 'index'])->name('visitors');
    Route::get('/visitors/live-count', [VisitorAnalyticsController::class, 'getLiveCount'])->name('visitors.live-count');
    Route::get('/visitors/export', [VisitorAnalyticsController::class, 'export'])->name('visitors.export');

    // Login Activity & Presence (who logs in / who is online)
    Route::get('/login-activity', [App\Http\Controllers\Admin\LoginActivityController::class, 'index'])->name('login_activity.index');

    // Provider Activity Monitor (WhatsApp Clicks & Views) — read-only analytics
    Route::get('/provider-activity-monitor', [App\Http\Controllers\Admin\ProviderActivityMonitorController::class, 'index'])->name('provider_activity_monitor.index');
    Route::get('/provider-activity-monitor/{serviceProvider}', [App\Http\Controllers\Admin\ProviderActivityMonitorController::class, 'show'])->name('provider_activity_monitor.show');

    // Provider Management (write actions on the listing itself)
    Route::post('/providers/bulk', [App\Http\Controllers\Admin\AdminProviderController::class, 'bulk'])->name('providers.bulk');
    Route::get('/providers/{serviceProvider}/edit', [App\Http\Controllers\Admin\AdminProviderController::class, 'edit'])->name('providers.edit');
    Route::patch('/providers/{serviceProvider}', [App\Http\Controllers\Admin\AdminProviderController::class, 'update'])->name('providers.update');
    Route::patch('/providers/{serviceProvider}/toggle-active', [App\Http\Controllers\Admin\AdminProviderController::class, 'toggleActive'])->name('providers.toggle_active');
    Route::delete('/providers/{serviceProvider}', [App\Http\Controllers\Admin\AdminProviderController::class, 'destroy'])->name('providers.destroy');

    // WhatsApp Click Intelligence (read-only analytics)
    Route::get('/whatsapp-analytics', [App\Http\Controllers\Admin\WhatsappAnalyticsController::class, 'index'])->name('whatsapp_analytics.index');

    // Utility - Clear caches to ensure admin changes reflect immediately
    Route::post('/clear-cache', [AdminMaintenanceController::class, 'clearCache'])->name('clear-cache');

    // Reviews Management (Admin approval workflow)
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews');
    Route::post('/reviews/bulk', [AdminReviewController::class, 'bulk'])->name('reviews.bulk');
    Route::get('/reviews/{review}', [AdminReviewController::class, 'show'])->name('reviews.show');
    Route::post('/reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{review}/reject', [AdminReviewController::class, 'reject'])->name('reviews.reject');
    Route::post('/reviews/{review}/feature', [AdminReviewController::class, 'feature'])->name('reviews.feature');
    Route::post('/reviews/{review}/unfeature', [AdminReviewController::class, 'unfeature'])->name('reviews.unfeature');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'delete'])->name('reviews.delete');

    // Comments Management (Admin approval workflow)
    Route::get('/comments', [AdminCommentController::class, 'index'])->name('comments');
    Route::post('/comments/bulk', [AdminCommentController::class, 'bulk'])->name('comments.bulk');
    Route::get('/comments/{comment}', [AdminCommentController::class, 'show'])->name('comments.show');
    Route::post('/comments/{comment}/approve', [AdminCommentController::class, 'approve'])->name('comments.approve');
    Route::post('/comments/{comment}/reject', [AdminCommentController::class, 'reject'])->name('comments.reject');
    Route::post('/comments/{comment}/flag', [AdminCommentController::class, 'flag'])->name('comments.flag');
    Route::post('/comments/{comment}/unflag', [AdminCommentController::class, 'unflag'])->name('comments.unflag');
    Route::delete('/comments/{comment}', [AdminCommentController::class, 'delete'])->name('comments.delete');
    Route::post('/comments/{comment}/restore', [AdminCommentController::class, 'restore'])->name('comments.restore');

    // Site Testimonials (admin-authored provider testimonials shown on home)
    Route::post('/testimonials/bulk', [App\Http\Controllers\Admin\AdminTestimonialController::class, 'bulk'])->name('testimonials.bulk');
    Route::get('/testimonials', [App\Http\Controllers\Admin\AdminTestimonialController::class, 'index'])->name('testimonials.index');
    Route::post('/testimonials', [App\Http\Controllers\Admin\AdminTestimonialController::class, 'store'])->name('testimonials.store');
    Route::patch('/testimonials/{testimonial}', [App\Http\Controllers\Admin\AdminTestimonialController::class, 'update'])->name('testimonials.update');
    Route::patch('/testimonials/{testimonial}/toggle', [App\Http\Controllers\Admin\AdminTestimonialController::class, 'toggle'])->name('testimonials.toggle');
    Route::delete('/testimonials/{testimonial}', [App\Http\Controllers\Admin\AdminTestimonialController::class, 'destroy'])->name('testimonials.destroy');

    // Home page video section (single record — no index/create, just an editor)
    Route::get('/home-video', [App\Http\Controllers\Admin\AdminHomeVideoController::class, 'edit'])->name('home_video.edit');
    Route::match(['put', 'post'], '/home-video', [App\Http\Controllers\Admin\AdminHomeVideoController::class, 'update'])
        ->middleware('handle.large.uploads')
        ->name('home_video.update');
    Route::delete('/home-video/file', [App\Http\Controllers\Admin\AdminHomeVideoController::class, 'destroyVideo'])->name('home_video.destroy_file');
    Route::delete('/home-video/poster', [App\Http\Controllers\Admin\AdminHomeVideoController::class, 'destroyPoster'])->name('home_video.destroy_poster');

    // Local Offers (home section settings, offers, statistics, claim codes)
    Route::get('/offers', [App\Http\Controllers\Admin\AdminOfferController::class, 'index'])->name('offers.index');
    Route::put('/offers/section', [App\Http\Controllers\Admin\AdminOfferController::class, 'updateSection'])->name('offers.section');
    Route::post('/offers/bulk', [App\Http\Controllers\Admin\AdminOfferController::class, 'bulk'])->name('offers.bulk');
    Route::post('/offers/reorder', [App\Http\Controllers\Admin\AdminOfferController::class, 'reorder'])->name('offers.reorder');
    Route::get('/offers/analytics', [App\Http\Controllers\Admin\AdminOfferAnalyticsController::class, 'index'])->name('offers.analytics');
    Route::get('/offers/comments', [App\Http\Controllers\Admin\AdminOfferCommentController::class, 'index'])->name('offers.comments');
    Route::post('/offers/comments/bulk', [App\Http\Controllers\Admin\AdminOfferCommentController::class, 'bulk'])->name('offers.comments.bulk');
    Route::patch('/offers/comments/{comment}/approve', [App\Http\Controllers\Admin\AdminOfferCommentController::class, 'approve'])->name('offers.comments.approve');
    Route::patch('/offers/comments/{comment}/reject', [App\Http\Controllers\Admin\AdminOfferCommentController::class, 'reject'])->name('offers.comments.reject');
    Route::delete('/offers/comments/{comment}', [App\Http\Controllers\Admin\AdminOfferCommentController::class, 'destroy'])->name('offers.comments.destroy');
    Route::get('/offers/create', [App\Http\Controllers\Admin\AdminOfferController::class, 'create'])->name('offers.create');
    Route::post('/offers', [App\Http\Controllers\Admin\AdminOfferController::class, 'store'])
        ->middleware('handle.large.uploads')
        ->name('offers.store');
    Route::get('/offers/{offer}/edit', [App\Http\Controllers\Admin\AdminOfferController::class, 'edit'])->name('offers.edit');
    Route::match(['put', 'post'], '/offers/{offer}', [App\Http\Controllers\Admin\AdminOfferController::class, 'update'])
        ->middleware('handle.large.uploads')
        ->name('offers.update');
    Route::post('/offers/{offer}/duplicate', [App\Http\Controllers\Admin\AdminOfferController::class, 'duplicate'])->name('offers.duplicate');
    Route::patch('/offers/{offer}/toggle', [App\Http\Controllers\Admin\AdminOfferController::class, 'toggle'])->name('offers.toggle');
    Route::patch('/offers/{offer}/feature', [App\Http\Controllers\Admin\AdminOfferController::class, 'feature'])->name('offers.feature');
    Route::delete('/offers/{offer}', [App\Http\Controllers\Admin\AdminOfferController::class, 'destroy'])->name('offers.destroy');

    // Notifications Management
    Route::post('/notifications/bulk', [App\Http\Controllers\Admin\AdminNotificationController::class, 'bulk'])->name('notifications.bulk');
    Route::get('/notifications', [App\Http\Controllers\Admin\AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/create', [App\Http\Controllers\Admin\AdminNotificationController::class, 'create'])->name('notifications.create');
    Route::get('/notifications/{notification}/read-receipts', [App\Http\Controllers\Admin\AdminNotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications', [App\Http\Controllers\Admin\AdminNotificationController::class, 'store'])->name('notifications.store');
    Route::delete('/notifications/{notification}', [App\Http\Controllers\Admin\AdminNotificationController::class, 'destroy'])->name('notifications.destroy');

    // Email Templates (admin-editable copy for the automated emails)
    Route::get('/email-templates', [App\Http\Controllers\Admin\AdminEmailTemplateController::class, 'index'])->name('email_templates.index');
    Route::get('/email-templates/{key}/edit', [App\Http\Controllers\Admin\AdminEmailTemplateController::class, 'edit'])->name('email_templates.edit');
    Route::put('/email-templates/{key}', [App\Http\Controllers\Admin\AdminEmailTemplateController::class, 'update'])->name('email_templates.update');
    Route::delete('/email-templates/{key}', [App\Http\Controllers\Admin\AdminEmailTemplateController::class, 'reset'])->name('email_templates.reset');
    Route::get('/email-templates/{key}/preview', [App\Http\Controllers\Admin\AdminEmailTemplateController::class, 'preview'])->name('email_templates.preview');

    // Email Journey Onboarding
    Route::get('/email-journey', [App\Http\Controllers\Admin\AdminEmailJourneyController::class, 'index'])->name('email_journey.index');
    Route::get('/email-journey/preview/{type}', [App\Http\Controllers\Admin\AdminEmailJourneyController::class, 'preview'])->name('email_journey.preview');
    Route::get('/email-journey/{serviceProvider}', [App\Http\Controllers\Admin\AdminEmailJourneyController::class, 'show'])->name('email_journey.show');
    Route::post('/email-journey/{serviceProvider}/send-test', [App\Http\Controllers\Admin\AdminEmailJourneyController::class, 'sendTest'])->name('email_journey.send_test');

    // Provider Broadcasts (admin writes a one-off email and sends it to providers)
    // The literal /preview segment is declared before the {broadcast} wildcard
    // so it is not swallowed by model binding.
    Route::post('broadcasts/preview', [App\Http\Controllers\Admin\AdminProviderBroadcastController::class, 'preview'])->name('broadcasts.preview');
    Route::post('broadcasts/{broadcast}/send-test', [App\Http\Controllers\Admin\AdminProviderBroadcastController::class, 'sendTest'])->name('broadcasts.send_test');
    Route::post('broadcasts/{broadcast}/send', [App\Http\Controllers\Admin\AdminProviderBroadcastController::class, 'send'])->name('broadcasts.send');
    Route::get('broadcasts/{broadcast}/progress', [App\Http\Controllers\Admin\AdminProviderBroadcastController::class, 'progress'])->name('broadcasts.progress');
    Route::resource('broadcasts', App\Http\Controllers\Admin\AdminProviderBroadcastController::class)
        ->parameters(['broadcasts' => 'broadcast'])
        ->names('broadcasts');
});

// Service Provider Notification Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
});


// ==================== CSRF TOKEN ====================
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf-token');
