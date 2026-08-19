<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsApproved
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->environment('production') && Auth::check() && !Auth::user()->approved_at) {
            Auth::logout();
            try {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            } catch (\Throwable $e) {
                // Session may not be available in test environment
            }

            if ($request->header('X-Inertia')) {
                return redirect()->route('login')->with('error', 'Your account is pending approval. Please contact an administrator.');
            }

            if ($request->expectsJson()) {
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
