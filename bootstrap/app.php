<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

if (!function_exists('friendlyMessageForStatus')) {
    function friendlyMessageForStatus(int $status): string
    {
        return match ($status) {
            401 => 'You must be signed in to do that. Please sign in and try again.',
            403 => 'You do not have permission to perform that action.',
            404 => 'We could not find the page you were looking for.',
            419 => 'Your session has expired. Please refresh the page and try again.',
            422 => 'There was a problem with the information you provided. Please check and try again.',
            429 => 'You are doing that too often. Please wait a moment and try again.',
            503 => 'This service is temporarily unavailable. Please try again later.',
            default => 'An unexpected error occurred. Please try again later.',
        };
    }
}

if (!function_exists('friendlyTitleForStatus')) {
    function friendlyTitleForStatus(int $status): string
    {
        return match ($status) {
            401 => 'Sign in required',
            403 => 'Access denied',
            404 => 'Page not found',
            419 => 'Session expired',
            422 => 'Invalid data',
            429 => 'Too many requests',
            503 => 'Service unavailable',
            default => 'Something went wrong',
        };
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders()
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\RequestLogging::class,
        ]);

        $middleware->alias([
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'registered' => \App\Http\Middleware\EnsureStudentIsRegistered::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Configure custom rendering for all requests
        $exceptions->render(function (\Throwable $e, Request $request) {
            // Let Laravel handle ValidationException normally
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return null;
            }

            $errorId = (string) Str::uuid();

            // Log full exception details (excluding sensitive data)
            \Illuminate\Support\Facades\Log::error('exception.reported', [
                'error_id' => $errorId,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => $request->user()?->id,
                'input' => $request->except(['password', 'password_confirmation', 'current_password', 'token']),
            ]);

            $status = 500;
            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
            }

            $message = friendlyMessageForStatus($status);
            $title = friendlyTitleForStatus($status);

            // Inertia request
            if ($request->header('X-Inertia')) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => $message,
                        'error_id' => $errorId,
                    ], $status);
                }

                $backUrl = url()->previous();
                $redirect = $backUrl ? redirect($backUrl) : redirect('/');

                return $redirect->with('error', [
                    'title' => $title,
                    'message' => $message,
                    'error_id' => $errorId,
                ]);
            }

            // AJAX / JSON request
            if ($request->expectsJson() || $request->isJson() || $request->ajax()) {
                return response()->json([
                    'message' => $message,
                    'error_id' => $errorId,
                ], $status);
            }

            // HTTP exception with built-in error page
            if ($e instanceof HttpExceptionInterface) {
                $errorView = "errors.{$status}";
                if (app()->bound('view') && view()->exists($errorView)) {
                    return response()->view($errorView, [
                        'message' => $message,
                        'errorId' => $errorId,
                    ], $status);
                }
            }

            // Fallback to generic error page
            if (app()->bound('view') && view()->exists('errors.500')) {
                return response()->view('errors.500', [
                    'message' => $message,
                    'errorId' => $errorId,
                ], $status);
            }

            // Ultimate fallback - plain text response when no views available
            return response($message, $status);
        });

        // Also report via LogExceptions for additional logging if needed
        $exceptions->reportable(function (\Throwable $e) {
            app(\App\Exceptions\LogExceptions::class)($e);
        });
    })->create();
