<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = current_school_id();

        $stats = [
            'total_students' => DB::table('students')
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->where('status', 'active')->count(),
            'total_teachers' => DB::table('teachers')
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->where('status', 'active')->count(),
            'total_classes' => DB::table('classes')
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->count(),
            'fee_collected' => DB::table('payment_transactions')
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->where('status', 'success')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'today_attendance' => DB::table('student_attendance')
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->where('date', now()->toDateString())
                ->where('status', 'present')->count(),
            'today_absent' => DB::table('student_attendance')
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->where('date', now()->toDateString())
                ->where('status', 'absent')->count(),
        ];

        // ─── School context for the welcome banner ──────────────────────────
        $school = $schoolId ? School::find($schoolId) : null;

        $banner = null;
        if ($school) {
            $maxStudents = (int) ($school->max_students ?: 0);
            $maxStaff    = (int) ($school->max_staff ?: 0);

            $studentUsagePct = $maxStudents > 0
                ? min(100, round(($stats['total_students'] / $maxStudents) * 100))
                : 0;
            $staffUsagePct = $maxStaff > 0
                ? min(100, round(($stats['total_teachers'] / $maxStaff) * 100))
                : 0;

            $daysToExpiry = $school->subscription_end
                ? now()->startOfDay()->diffInDays($school->subscription_end, false)
                : null;

            $banner = [
                'name'              => $school->name,
                'code'              => $school->code,
                'logo'              => $school->logo,
                'city'              => $school->city,
                'state'             => $school->state,
                'board'             => $school->board_affiliation,
                'principal'         => $school->principal_name,
                'phone'             => $school->phone,
                'email'             => $school->email,
                'plan'              => ucfirst($school->subscription_plan ?? 'basic'),
                'subscription_end'  => $school->subscription_end,
                'days_to_expiry'    => $daysToExpiry !== null ? (int) floor($daysToExpiry) : null,
                'max_students'      => $maxStudents,
                'max_staff'         => $maxStaff,
                'student_usage_pct' => $studentUsagePct,
                'staff_usage_pct'   => $staffUsagePct,
            ];
        }

        return view('dashboard', compact('stats', 'banner', 'school'));
    }
}
