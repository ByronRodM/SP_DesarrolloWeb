<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user || $user->rol !== 'admin') {
            return response()->json([
                'message' => 'No autorizado: se requiere rol admin.'
            ], 403);
        }
        return $next($request);
    }
}
