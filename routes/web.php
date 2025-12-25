<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceProviderController;
use Illuminate\Support\Facades\Route;

// Include authentication routes
require __DIR__.'/auth.php';

// ==================== LOCALE ROUTES ====================
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
Route::get('/current-locale', [LocaleController::class, 'getCurrentLocale'])->name('locale.current');

// ==================== PUBLIC ROUTES ====================
Route::view('/', 'home')->name('home');

// Test translation route
Route::get('/test-translations', function() {
    return response()->json([
        'roadside_en' => __('categories.roadside_assistance_24_7'),
        'accounting_en' => __('categories.accounting_bookkeeping_tax_preparation'),
        'roadside_ar' => trans('categories.roadside_assistance_24_7', [], 'ar'),
        'accounting_ar' => trans('categories.accounting_bookkeeping_tax_preparation', [], 'ar'),
    ]);
});

// Static pages
Route::get('/locations', [LocationController::class, 'index'])->name('location');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
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

// ==================== AUTHENTICATED ROUTES ====================
Route::middleware(['auth'])->get('/dashboard', function () {
    return redirect()->route('service-providers.index');
})->name('dashboard');

// ==================== USER PROFILE ROUTES ====================
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->prefix('service-providers')->group(function () {
    // View own profile - redirect to show page (with edit section for owner)
    Route::get('/profile', function () {
        $provider = auth()->user()->serviceProvider;
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

    // Handle profile image upload
    Route::post('/profile/image-upload', [ServiceProviderController::class, 'uploadProfileImage'])
        ->name('service-providers.profile.image-upload');
});

// ==================== CSRF TOKEN ====================
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf-token');
