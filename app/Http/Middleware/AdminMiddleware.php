<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('admin')->user() ?? Auth::user();

        if (! $user || ! $user->isStaff()) {
            abort(403, 'Unauthorized');
        }

        Auth::shouldUse(Auth::guard('admin')->check() ? 'admin' : 'web');

        return $next($request);
    }
}
