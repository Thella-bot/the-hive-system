<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestLogging
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        try {
            /** @var Response $response */
            $response = $next($request);

            $durationMs = (int) ((microtime(true) - $start) * 1000);

            Log::info('request.completed', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'path' => $request->path(),
                'route_name' => optional($request->route())->getName(),
                'status' => $response->getStatusCode(),
                'duration_ms' => $durationMs,
                'user_id' => optional($request->user())->id,
                'trace_id' => (string) $request->header('X-Request-Id') ?: (string) Str::uuid(),
            ]);

            return $response;
        } catch (\Throwable $e) {
            $durationMs = (int) ((microtime(true) - $start) * 1000);

            Log::error('request.failed', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'path' => $request->path(),
                'route_name' => optional($request->route())->getName(),
                'duration_ms' => $durationMs,
                'user_id' => optional($request->user())->id,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'trace_id' => (string) $request->header('X-Request-Id') ?: (string) Str::uuid(),
            ]);

            throw $e;
        }
    }
}

