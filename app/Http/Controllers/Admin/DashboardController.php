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

        $insights = $this->insights($schoolId);

        return view('dashboard', compact('stats', 'banner', 'school', 'insights'));
    }

    /**
     * Read-only widgets for the dashboard, built from existing tables only.
     * Every block degrades to an empty list if a table is missing.
     */
    private function insights(?int $schoolId): array
    {
        $scoped = fn (string $table) => DB::table($table)->when($schoolId, fn ($q) => $q->where($table . '.school_id', $schoolId));
        $has = fn (string $t) => \Illuminate\Support\Facades\Schema::hasTable($t);
        $today = now()->toDateString();

        // Student attendance over the last 7 days (present + late vs. marked).
        $trend = [];
        if ($has('student_attendance')) {
            $rows = $scoped('student_attendance')
                ->where('date', '>=', now()->subDays(6)->toDateString())
                ->selectRaw("date, COUNT(*) as marked, SUM(CASE WHEN status IN ('present','late') THEN 1 ELSE 0 END) as attended")
                ->groupBy('date')->get()->keyBy(fn ($r) => \Illuminate\Support\Carbon::parse($r->date)->toDateString());
            for ($i = 6; $i >= 0; $i--) {
                $d = now()->subDays($i);
                $r = $rows->get($d->toDateString());
                $trend[] = [
                    'label' => $d->format('D'),
                    'date' => $d->format('d M'),
                    'marked' => (int) ($r->marked ?? 0),
                    'pct' => $r && $r->marked > 0 ? (int) round($r->attended / $r->marked * 100) : null,
                ];
            }
        }

        $recentPayments = $has('payment_transactions')
            ? $scoped('payment_transactions')
                ->leftJoin('students', 'students.id', '=', 'payment_transactions.student_id')
                ->leftJoin('users', 'users.id', '=', 'students.user_id')
                ->where('payment_transactions.status', 'success')
                ->orderByDesc('payment_transactions.created_at')->limit(6)
                ->get(['payment_transactions.amount', 'payment_transactions.gateway', 'payment_transactions.payable_type', 'payment_transactions.created_at', 'users.name as student_name'])
            : collect();

        $holidays = $has('holidays')
            ? $scoped('holidays')->where('end_date', '>=', $today)->orderBy('start_date')->limit(4)
                ->get(['title', 'start_date', 'end_date', 'type', 'applies_to'])
            : collect();

        $events = $has('events')
            ? $scoped('events')->where('status', 'published')->where('start_at', '>=', now())->orderBy('start_at')->limit(4)
                ->get(['title', 'start_at', 'venue', 'category'])
            : collect();

        $pending = $has('fee_invoices')
            ? $scoped('fee_invoices')->where('status', 'pending')
                ->selectRaw('COUNT(*) as invoices, COALESCE(SUM(total_amount),0) as amount, COALESCE(SUM(CASE WHEN due_date < ? THEN total_amount ELSE 0 END),0) as overdue', [$today])
                ->first()
            : null;

        $staffToday = $has('staff_attendance')
            ? $scoped('staff_attendance')->where('date', $today)
                ->selectRaw("COUNT(*) as marked, SUM(CASE WHEN status IN ('present','late','half_day') THEN 1 ELSE 0 END) as present")
                ->first()
            : null;

        return compact('trend', 'recentPayments', 'holidays', 'events', 'pending', 'staffToday');
    }
}
