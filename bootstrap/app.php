<?php

use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackUserPresence;
use App\Http\Middleware\TrackVisitor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->call(function () {
            \App\Models\AdminNotification::where('expires_at', '<', now())->delete();
        })->daily();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // Add SetLocale, TrackVisitor, and CheckUserStatus to web middleware group
        $middleware->web(append: [
            SetLocale::class,
            TrackVisitor::class,
            CheckUserStatus::class, // Check if user account is active
            TrackUserPresence::class, // Presence heartbeat (last_seen_at)
        ]);

        // Gmail/Outlook one-click unsubscribe POSTs straight from the inbox and
        // carries no session or CSRF token. The URL's signature is the proof of
        // authenticity here, so CSRF has nothing to add.
        $middleware->validateCsrfTokens(except: [
            'email/unsubscribe/*',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'handle.large.uploads' => \App\Http\Middleware\HandleLargeUploads::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'check.user.status' => \App\Http\Middleware\CheckUserStatus::class,
            // Pins the admin panel to English regardless of site language.
            'admin.locale' => \App\Http\Middleware\ForceAdminLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle validation exceptions with inline errors
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }

            // For non-AJAX requests, redirect back with errors
            return redirect()->back()->withErrors($e->errors())->withInput();
        });

        // Handle authentication exceptions
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login')->with('error', 'Please log in to continue.');
        });

        // Handle authorization exceptions
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This action is unauthorized.'], 403);
            }

            return redirect()->back()->with('error', 'You are not authorized to perform this action.');
        });

        // Handle CSRF token exceptions with friendly messages
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session has expired. Please refresh the page and try again.',
                    'csrf_token' => csrf_token(),
                ], 419);
            }

            return redirect()->back()->with('error', 'Your session has expired. Please refresh the page and try again.');
        });

        // Handle PostTooLargeException with user-friendly messages
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The uploaded file is too large. Maximum allowed size is '.ini_get('upload_max_filesize').'.',
                    'max_size' => ini_get('upload_max_filesize'),
                ], 413);
            }

            return redirect()->back()->with('error', 'The uploaded file is too large. Please select a smaller image (max '.ini_get('upload_max_filesize').').');
        });

        // Handle model not found exceptions
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'The requested resource was not found.'], 404);
            }

            return redirect()->back()->with('error', 'The requested resource was not found.');
        });

        // Handle general exceptions with user-friendly messages
        $exceptions->render(function (\Exception $e, $request) {
            // Don't handle exceptions in debug mode
            if (config('app.debug')) {
                return null;
            }

            // Never swallow an HTTP exception. By the time this runs, Laravel
            // has already normalised "page missing", "not allowed", "token
            // expired" and friends into an HttpException carrying the status
            // the client must actually receive. Redirecting them instead turned
            // every 404 into a soft-404 pointing at the homepage — which search
            // engines index as a redirect — and downgraded 403s to 302s.
            //
            // Deliberately keyed on the interface, not on individual codes, so
            // every current and future HTTP status keeps its real semantics.
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                return null;
            }

            // Log the error for debugging
            \Illuminate\Support\Facades\Log::error('Application error: '.$e->getMessage(), [
                'exception' => $e,
                'url' => $request->url(),
                'user' => Auth::id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'An unexpected error occurred. Please try again later.',
                    'error_code' => 'INTERNAL_ERROR',
                ], 500);
            }

            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again later.');
        });
    })->create();
