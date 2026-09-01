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

        return view('dashboard', compact('stats'));
    }
}
