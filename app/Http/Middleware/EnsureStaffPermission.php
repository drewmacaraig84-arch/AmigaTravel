<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureStaffPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): mixed
    {
        $user = Auth::guard('admin')->user() ?? Auth::user();

        if (! $user || ! $user->isStaff()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            return redirect()->route('dashboard')->with('error', 'You do not have access to that feature.');
        }

        // Expand any comma-separated permissions
        $parsedPermissions = [];
        foreach ($permissions as $perm) {
            foreach (explode(',', $perm) as $single) {
                $trimmed = trim($single);
                if ($trimmed !== '') {
                    $parsedPermissions[] = $trimmed;
                }
            }
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        foreach ($parsedPermissions as $perm) {
            if ($user->hasAdminPermission($perm)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'You do not have permission to access this feature.'], 403);
        }

        // If user is already in admin area or has staff access, redirect to /admin instead of customer dashboard
        return redirect()->to('/admin')->with('error', 'You do not have access to that feature.');
    }
}
