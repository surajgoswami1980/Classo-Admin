<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolSwitchController extends Controller
{
    /**
     * Super admin "views as" a school: stays on the /admin/* tree (still
     * fully super-admin, unrestricted by team permissions) but every
     * controller now scopes to this one school via the
     * impersonating_school_id session key, which SuperAdminMiddleware and
     * SchoolTenantMiddleware both honor. Switching again to another school,
     * or exiting, immediately changes what's shown — nothing is cached per
     * request beyond the session key itself.
     */
    public function switch(Request $request, School $school)
    {
        abort_unless(auth()->user()->hasRole('super-admin'), 403);

        if (!$school->is_active) {
            return back()->with('error', 'Cannot view an inactive school.');
        }

        session([
            'impersonating_school_id' => $school->id,
            'impersonating_school_name' => $school->name,
        ]);

        // "Select a school" screen sends the page the admin was trying to open.
        // Only same-app /admin paths are honoured (no open redirect).
        $return = (string) $request->input('return_to', '');
        if ($return !== '' && str_starts_with($return, '/admin') && !str_starts_with($return, '//')) {
            return redirect($return)->with('success', 'Now viewing ' . $school->name . '.');
        }

        return redirect()->route('admin.dashboard');
    }

    public function exit(Request $request)
    {
        $request->session()->forget(['impersonating_school_id', 'impersonating_school_name']);

        return redirect()->route('admin.dashboard');
    }
}
