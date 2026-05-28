<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * Report or log an exception.
     */
    public function report(Throwable $e): void
    {
        $errorId = (string) Str::uuid();

        Log::error('exception.reported', [
            'error_id' => $errorId,
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'trace' => $e->getTraceAsString(),
        ]);

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        $errorId = (string) Str::uuid();

        // Log the full exception details
        Log::error('exception.reported', [
            'error_id' => $errorId,
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => $request->user()?->id,
        ]);

        $status = 500;
        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
        }

        // AJAX / JSON request
        if ($request->expectsJson() || $request->isJson() || $request->ajax()) {
            return response()->json([
                'message' => $this->friendlyMessageForStatus($status),
                'error_id' => $errorId,
            ], $status);
        }

        // Inertia request
        if (app()->bound('inertia') && $request->header('X-Inertia')) {
            $message = $this->friendlyMessageForStatus($status);

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'error_id' => $errorId,
                ], $status);
            }

            // Flash error message for Inertia
            if (inertia()->version()) {
                return redirect()->back()->with('error', [
                    'title' => $this->friendlyTitleForStatus($status),
                    'message' => $message,
                    'error_id' => $errorId,
                ]);
            }

            return redirect()->back()->with('error', $message);
        }

        // HTTP exception with built-in error page
        if ($e instanceof HttpExceptionInterface && view()->exists("errors.{$status}")) {
            return response()->view("errors.{$status}", [
                'message' => $this->friendlyMessageForStatus($status),
                'errorId' => $errorId,
            ], $status);
        }

        // Fallback to generic error page
        return response()->view('errors.500', [
            'message' => $this->friendlyMessageForStatus($status),
            'errorId' => $errorId,
        ], $status);
    }

    protected function friendlyMessageForStatus(int $status): string
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

    protected function friendlyTitleForStatus(int $status): string
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