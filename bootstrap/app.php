<?php

use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrackUserPresence;
use App\Http\Middleware\TrackVisitor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        // Add SetLocale, TrackVisitor, and CheckUserStatus to web middleware group
        $middleware->web(append: [
            SetLocale::class,
            TrackVisitor::class,
            CheckUserStatus::class, // Check if user account is active
            TrackUserPresence::class, // Presence heartbeat (last_seen_at)
            // Must run before the controller validates: Laravel's validator
            // collapses every PHP upload failure into one message that names no
            // cause. This replaces it with the actual reason, per field.
            \App\Http\Middleware\ExplainUploadFailures::class,
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

            return redirect()->route('login')->with('error', __('errors.unauthorized'));
        });

        // Laravel converts AuthorizationException, ModelNotFoundException and
        // TokenMismatchException into HttpExceptions (403 / 404 / 419) BEFORE
        // render callbacks run, so callbacks typed on those classes never fire.
        // A 403 and a 404 are left to the branded errors/*.blade.php pages; an
        // expired CSRF token is matched by its status code instead.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('errors.csrf_token_expired'),
                    'csrf_token' => csrf_token(),
                ], 419);
            }

            // A form submitted after the session timed out: send the person
            // back to it with what they typed, minus anything secret.
            return redirect()->back()
                ->withInput($request->except(['password', 'password_confirmation', 'current_password', '_token']))
                ->with('error', __('errors.csrf_token_expired'));
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

            // Logging is not done here: Laravel reports the exception on its
            // own (with the user id), and rendering it a second time only
            // doubled every entry in the log.

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('errors.server_error'),
                    'error_code' => 'INTERNAL_ERROR',
                ], 500);
            }

            // A page that failed to load gets a real 500 page. Redirecting it
            // "back" sent visitors from Google straight back to Google (back()
            // follows the Referer), looped when the failing page was the one
            // they came from, and hid every failure from uptime monitoring
            // behind a 302.
            if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
                return null;
            }

            // A failed form submission returns to the form with its input.
            return redirect()->back()
                ->withInput($request->except(['password', 'password_confirmation', 'current_password', '_token']))
                ->with('error', __('errors.server_error'));
        });
    })->create();
