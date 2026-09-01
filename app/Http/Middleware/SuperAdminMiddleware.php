<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only super-admin can access /admin/* routes.
 * School users use /user/* routes.
 */
class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->hasRole('super-admin')) {
            // If school-admin tries to access /admin/*, redirect to /user/ equivalent
            $path = $request->path();
            $userPath = preg_replace('/^admin\//', 'user/', $path);
            return redirect('/' . $userPath);
        }

        // A super-admin who has switched into a school (see SchoolSwitchController)
        // keeps browsing under /admin/* — bind the school context so every
        // controller's current_school_id() and the BelongsToSchool global
        // scope resolve to that school instead of the unscoped global view.
        $schoolId = session('impersonating_school_id');
        if ($schoolId) {
            $request->attributes->set('school_id', $schoolId);
            app()->instance('current_school_id', $schoolId);
        }

        return $next($request);
    }
}
