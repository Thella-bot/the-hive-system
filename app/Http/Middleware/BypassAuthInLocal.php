<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BypassAuthInLocal
{
    public function handle(Request $request, Closure $next): Response
    {
        // SECURITY: Only allow in true local environment, never in production/staging
        // This middleware should NEVER be used in production - disable it in config
        if ($request->expectsJson() && app()->environment('local')) {
            // Require explicit X-Dev-User-Id header - do NOT fallback to first user
            $userId = $request->header('X-Dev-User-Id');

            if ($userId && is_numeric($userId)) {
                // SECURITY: Log all dev auth bypass attempts for audit trail
                \Log::info('Dev auth bypass attempted', [
                    'user_id' => $userId,
                    'ip' => $request->ip(),
                    'route' => $request->path(),
                ]);

                // Find user by ID only (no email or other identifiers)
                $user = \App\Models\User::find($userId);

                if ($user) {
                    // Set user ONLY if explicitly specified - never auto-fallback
                    auth()->setUser($user);
                }
            }
        }

        return $next($request);
    }
}