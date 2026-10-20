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
            // Real platform totals (previously hardcoded to 0).
            'total_students_platform' => \Illuminate\Support\Facades\DB::table('students')->where('status', 'active')->count(),
            // Same definition as the Revenue page (successful payments this month).
            'monthly_revenue' => (float) \Illuminate\Support\Facades\DB::table('payment_transactions')->where('status', 'success')
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
            'new_enquiries' => \Illuminate\Support\Facades\Schema::hasTable('enquiries')
                ? \Illuminate\Support\Facades\DB::table('enquiries')->where('status', 'new')->count() : 0,
            'expiring_soon' => School::whereNotNull('subscription_end')
                ->whereBetween('subscription_end', [now()->toDateString(), now()->addDays(30)->toDateString()])->count(),
            'new_schools_this_month' => School::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $recentSchools = School::latest()->take(10)->get();

        return view('super-admin.dashboard', compact('stats', 'recentSchools'));
    }
}
