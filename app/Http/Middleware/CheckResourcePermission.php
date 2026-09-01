<?php

namespace App\Http\Middleware;

use App\Support\ResourceSlug;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces the resource-action permission engine (Resource -> ResourceAction
 * -> ResourcePermission, granted via a Policy attached to a Role or directly
 * to a user) on the /user/* route tree. Super-admin and school-admin always
 * bypass — this only restricts sub-admin / incharge team members.
 */
class CheckResourcePermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        if ($user->hasRole('super-admin') || $user->hasRole('school-admin')) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if (!$routeName) {
            return $next($request);
        }

        $name = str_starts_with($routeName, 'user.') ? substr($routeName, 5) : $routeName;

        if (in_array($name, ResourceSlug::$except, true)) {
            return $next($request);
        }

        $slug = ResourceSlug::canonicalize($name);

        if (has_resource_permission($slug)) {
            return $next($request);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        return response()->view('errors.403', [], 403);
    }
}
