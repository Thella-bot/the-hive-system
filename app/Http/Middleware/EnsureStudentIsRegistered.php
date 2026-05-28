<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentIsRegistered
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('student') && $user->needsRegistration()) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'You must complete your registration before accessing this feature.',
                ], 403);
            }

            return redirect()->route('hive.registration.index')
                ->with('error', 'Please complete your registration before accessing modules and grades.');
        }

        return $next($request);
    }
}
