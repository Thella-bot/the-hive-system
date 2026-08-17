<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsApproved
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && !Auth::user()->approved_at) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'Your account is pending approval. Please contact an administrator.',
                    'error_id' => 'account_pending_approval',
                ], 403);
            }

            return redirect('/login')->with('error', 'Your account is pending approval. Please contact an administrator.');
        }

        return $next($request);
    }
}
