<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureRole Middleware
 *
 * SUPER ADMIN: Bypasses ALL permission/role checks. Full platform access.
 * SCHOOL ADMIN: Has all permissions within their school by default.
 * SUB ADMIN / INCHARGE / STAFF: Only have permissions assigned by super-admin.
 *
 * Permissions are toggle-based per school — super admin controls which
 * menus/features each school can access (like Educrypt's admin panel).
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // Super Admin ALWAYS bypasses — no permission checks ever
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if (!empty($roles) && !$user->hasAnyRole($roles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
