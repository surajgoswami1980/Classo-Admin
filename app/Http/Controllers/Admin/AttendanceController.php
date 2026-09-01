<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    private function getSchoolId(): ?int
    {
        // current_school_id() is impersonation-aware: while a super-admin is
        // switched into a school, this scopes to that school; otherwise
        // (no impersonation, own school_id null) it stays null -> global view.
        return current_school_id();
    }

    public function index()
    {
        return view('attendance.index');
    }

    public function studentAttendance(Request $request)
    {
        $schoolId = $this->getSchoolId();
        $classes = DB::table('classes')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('numeric_order')
            ->pluck('name', 'id');

        return view('admin.attendance.students', compact('classes'));
    }

    public function markStudentAttendance(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'date' => 'required|date|before_or_equal:today',
            'attendance' => 'required|array|min:1',
            'attendance.*.student_id' => 'required|integer',
            'attendance.*.status' => 'required|in:present,absent,late,half_day',
        ], [
            'date.before_or_equal' => 'Cannot mark attendance for a future date.',
            'attendance.required' => 'No attendance data provided.',
        ]);

        $schoolId = $this->getSchoolId();
        $markedBy = auth()->id();
        $count = 0;

        foreach ($validated['attendance'] as $item) {
            DB::table('student_attendance')->updateOrInsert(
                [
                    'student_id' => $item['student_id'],
                    'date' => $validated['date'],
                ],
                [
                    'school_id' => $schoolId ?? DB::table('students')->where('id', $item['student_id'])->value('school_id'),
                    'class_id' => $validated['class_id'],
                    'section_id' => $validated['section_id'],
                    'status' => $item['status'],
                    'marked_by' => $markedBy,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $count++;
        }

        return back()->with('success', "Attendance marked for {$count} students");
    }

    public function staffAttendance(Request $request)
    {
        $schoolId = $this->getSchoolId();
        $staff = DB::table('users')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->whereIn('id', function ($q) {
                $q->select('model_id')->from('model_has_roles')
                  ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                  ->whereIn('roles.name', ['teacher', 'school-admin', 'sub-admin']);
            })
            ->where('is_active', true)
            ->select(['id', 'name', 'email', 'employee_id'])
            ->get();

        return view('admin.attendance.staff', compact('staff'));
    }

    public function markStaffAttendance(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'attendance' => 'required|array|min:1',
            'attendance.*.user_id' => 'required|integer|exists:users,id',
            'attendance.*.status' => 'required|in:present,absent,leave,half_day,late',
        ]);

        $schoolId = $this->getSchoolId();
        $markedBy = auth()->id();
        $count = 0;

        foreach ($validated['attendance'] as $item) {
            DB::table('staff_attendance')->updateOrInsert(
                [
                    'user_id' => $item['user_id'],
                    'date' => $validated['date'],
                ],
                [
                    'school_id' => $schoolId ?? DB::table('users')->where('id', $item['user_id'])->value('school_id'),
                    'status' => $item['status'],
                    'check_in_time' => $item['check_in_time'] ?? null,
                    'check_out_time' => $item['check_out_time'] ?? null,
                    'marked_by' => $markedBy,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $count++;
        }

        return back()->with('success', "Staff attendance marked for {$count} members");
    }

    public function report(Request $request)
    {
        $filters = $request->only(['class_id', 'section_id', 'from_date', 'to_date', 'type']);
        $schoolId = $this->getSchoolId();
        $report = collect();

        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
            $type = $filters['type'] ?? 'student';

            if ($type === 'student') {
                $report = DB::table('student_attendance')
                    ->join('students', 'student_attendance.student_id', '=', 'students.id')
                    ->join('users', 'students.user_id', '=', 'users.id')
                    ->when($schoolId, fn($q) => $q->where('student_attendance.school_id', $schoolId))
                    ->when(!empty($filters['class_id']), fn($q) => $q->where('student_attendance.class_id', $filters['class_id']))
                    ->whereBetween('student_attendance.date', [$filters['from_date'], $filters['to_date']])
                    ->select([
                        'students.id as student_id',
                        'users.name',
                        'students.roll_number',
                        DB::raw("SUM(CASE WHEN student_attendance.status = 'present' THEN 1 ELSE 0 END) as present_days"),
                        DB::raw("SUM(CASE WHEN student_attendance.status = 'absent' THEN 1 ELSE 0 END) as absent_days"),
                        DB::raw("SUM(CASE WHEN student_attendance.status = 'late' THEN 1 ELSE 0 END) as late_days"),
                        DB::raw("COUNT(*) as total_days"),
                    ])
                    ->groupBy('students.id', 'users.name', 'students.roll_number')
                    ->get();
            } else {
                $report = DB::table('staff_attendance')
                    ->join('users', 'staff_attendance.user_id', '=', 'users.id')
                    ->when($schoolId, fn($q) => $q->where('staff_attendance.school_id', $schoolId))
                    ->whereBetween('staff_attendance.date', [$filters['from_date'], $filters['to_date']])
                    ->select([
                        'users.id as user_id',
                        'users.name',
                        'users.employee_id',
                        DB::raw("SUM(CASE WHEN staff_attendance.status = 'present' THEN 1 ELSE 0 END) as present_days"),
                        DB::raw("SUM(CASE WHEN staff_attendance.status = 'absent' THEN 1 ELSE 0 END) as absent_days"),
                        DB::raw("SUM(CASE WHEN staff_attendance.status = 'leave' THEN 1 ELSE 0 END) as leave_days"),
                        DB::raw("COUNT(*) as total_days"),
                    ])
                    ->groupBy('users.id', 'users.name', 'users.employee_id')
                    ->get();
            }
        }

        $classes = DB::table('classes')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->pluck('name', 'id');

        return view('admin.attendance.report', compact('report', 'filters', 'classes'));
    }
}
