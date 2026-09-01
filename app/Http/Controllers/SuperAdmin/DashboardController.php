<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\DashboardController as SchoolDashboardController;
use App\Models\School;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // While impersonating a school, /admin/dashboard shows that
        // school's own dashboard (same one /user/dashboard shows) instead
        // of this platform-wide view — the platform totals below don't
        // mean anything scoped to a single school.
        if (is_impersonating()) {
            return app(SchoolDashboardController::class)->index($request);
        }

        $stats = [
            'total_schools' => School::count(),
            'active_subscriptions' => School::where('is_active', true)->count(),
            'total_students_platform' => 0,
            'monthly_revenue' => 0,
            'new_schools_this_month' => School::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $recentSchools = School::latest()->take(10)->get();

        return view('super-admin.dashboard', compact('stats', 'recentSchools'));
    }
}
