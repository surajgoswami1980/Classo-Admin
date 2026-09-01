<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SchoolTenantMiddleware
 *
 * Scopes all database queries to the authenticated user's school_id.
 * This ensures multi-tenant data isolation at the application level.
 *
 * The school_id is resolved from:
 * 1. The authenticated user's school_id attribute
 * 2. The session (for cases where school context is switched by super-admin)
 *
 * Super admins can optionally impersonate a school context via session.
 */
class SchoolTenantMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // Super admins bypass tenant scoping unless they're impersonating a school
        if ($user->hasRole('super-admin')) {
            $schoolId = session('impersonating_school_id');
            if ($schoolId) {
                $this->setSchoolContext($request, $schoolId);
            }
            return $next($request);
        }

        // School admin, sub-admin, incharge must have a school_id
        $schoolId = $user->school_id;

        if (!$schoolId) {
            abort(403, 'No school assigned to this account. Contact your administrator.');
        }

        // Verify the school is active
        $school = \App\Models\School::find($schoolId);
        if (!$school || !$school->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['school' => 'Your school account has been deactivated.']);
        }

        $this->setSchoolContext($request, $schoolId);

        return $next($request);
    }

    /**
     * Set the school context for the current request.
     */
    protected function setSchoolContext(Request $request, int|string $schoolId): void
    {
        // Store in request for easy access in controllers
        $request->attributes->set('school_id', $schoolId);

        // Store in session for persistence across requests
        session(['current_school_id' => $schoolId]);

        // Apply global scope to Eloquent models that use the BelongsToSchool trait
        app()->instance('current_school_id', $schoolId);
    }
}
