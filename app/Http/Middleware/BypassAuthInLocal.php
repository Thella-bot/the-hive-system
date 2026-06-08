<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BypassAuthInLocal
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only bypass auth in local environment
        if ($request->expectsJson() && app()->environment('local')) {
            // For development: mock authenticated user from request header or default
            $userId = $request->header('X-Dev-User-Id');

            if ($userId && $user = \App\Models\User::find($userId)) {
                auth()->setUser($user);
            } elseif ($user = \App\Models\User::first()) {
                // Default to first user if no header provided
                auth()->setUser($user);
            }
        }

        return $next($request);
    }
}