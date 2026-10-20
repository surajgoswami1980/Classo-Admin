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
        $schoolId = $this->getSchoolId();

        $navClasses = DB::table('classes')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('numeric_order')
            ->pluck('name', 'id');

        $navSections = DB::table('sections')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('name')
            ->get(['id', 'name', 'class_id']);

        return view('attendance.index', compact('navClasses', 'navSections'));
    }

    public function studentAttendance(Request $request)
    {
        $schoolId = $this->getSchoolId();
        $classes = DB::table('classes')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('numeric_order')
            ->pluck('name', 'id');

        // All sections for the school so the UI can filter them by the chosen
        // class entirely client-side (no extra round-trip).
        $sections = DB::table('sections')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('name')
            ->get(['id', 'name', 'class_id']);

        return view('admin.attendance.students', compact('classes', 'sections'));
    }

    /**
     * AJAX: students of a class/section merged with any attendance already
     * recorded for a given date. Lets the admin review/complete an existing
     * day's record and mark previous days.
     */
    public function studentRoster(Request $request)
    {
        $classId = (int) $request->get('class_id');
        $sectionId = (int) $request->get('section_id');
        $date = $request->get('date');
        $schoolId = $this->getSchoolId();

        if (!$classId || !$sectionId) {
            return response()->json(['data' => [], 'has_record' => false]);
        }

        $students = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->where('students.class_id', $classId)
            ->where('students.section_id', $sectionId)
            ->when($schoolId, fn($q) => $q->where('students.school_id', $schoolId))
            ->where('students.status', 'active')
            ->select(['students.id', 'students.roll_number', 'users.name'])
            ->orderBy('students.roll_number')
            ->get();

        // Existing marks for the date → so admin sees what's already recorded.
        $existing = [];
        if ($date) {
            $existing = DB::table('student_attendance')
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->whereDate('date', $date)
                ->pluck('status', 'student_id')
                ->toArray();
        }

        $data = $students->map(function ($s) use ($existing) {
            return [
                'id' => $s->id,
                'roll_number' => $s->roll_number,
                'name' => $s->name,
                'status' => $existing[$s->id] ?? 'present',
                'recorded' => isset($existing[$s->id]),
            ];
        });

        return response()->json([
            'data' => $data,
            'has_record' => count($existing) > 0,
            'day_off' => $date ? \App\Support\AttendanceRules::dayOff($schoolId, $date, 'students') : null,
        ]);
    }

    /**
     * AJAX: staff list merged with any attendance already recorded for a date.
     */
    public function staffRoster(Request $request)
    {
        $date = $request->get('date');
        $schoolId = $this->getSchoolId();

        $staff = DB::table('users')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->whereIn('id', function ($q) {
                $q->select('model_id')->from('model_has_roles')
                  ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                  ->whereIn('roles.name', ['teacher', 'incharge', 'staff', 'school-admin', 'sub-admin']);
            })
            ->where('is_active', true)
            ->select(['id', 'name', 'email', 'employee_id'])
            ->orderBy('name')
            ->get();

        $existing = collect();
        if ($date) {
            $existing = DB::table('staff_attendance')
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->whereDate('date', $date)
                ->get(['user_id', 'status', 'method', 'check_in_time', 'check_out_time', 'late_minutes', 'check_in_distance', 'face_score', 'remarks'])
                ->keyBy('user_id');
        }

        $tz = \App\Support\AttendanceRules::forSchool($schoolId)['timezone'];
        $fmt = fn ($t) => $t ? \Carbon\Carbon::parse($t)->timezone($tz)->format('h:i A') : null;

        $data = $staff->map(function ($m) use ($existing, $fmt) {
            $r = $existing[$m->id] ?? null;
            return [
                'id' => $m->id,
                'employee_id' => $m->employee_id,
                'name' => $m->name,
                'status' => $r->status ?? 'present',
                'recorded' => (bool) $r,
                'method' => $r->method ?? null,
                'check_in' => $fmt($r->check_in_time ?? null),
                'check_out' => $fmt($r->check_out_time ?? null),
                'late_minutes' => $r->late_minutes ?? null,
                'distance' => $r->check_in_distance ?? null,
                'face_score' => $r->face_score ?? null,
                'remarks' => $r->remarks ?? null,
            ];
        });

        $off = $date ? \App\Support\AttendanceRules::dayOff($schoolId, $date, 'staff') : null;

        return response()->json([
            'data' => $data,
            'has_record' => $existing->count() > 0,
            'day_off' => $off,
        ]);
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
        if ($off = \App\Support\AttendanceRules::dayOff($schoolId, $validated['date'], 'students')) {
            return back()->withInput()->with('error', \App\Support\AttendanceRules::offMessage($validated['date'], $off));
        }
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
                  ->whereIn('roles.name', ['teacher', 'incharge', 'staff', 'school-admin', 'sub-admin']);
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
            'attendance.*.remarks' => 'nullable|string|max:255',
        ]);

        $schoolId = $this->getSchoolId();
        if ($off = \App\Support\AttendanceRules::dayOff($schoolId, $validated['date'], 'staff')) {
            return back()->withInput()->with('error', \App\Support\AttendanceRules::offMessage($validated['date'], $off));
        }
        $markedBy = auth()->id();
        $count = 0;
        $changed = 0;

        $existing = DB::table('staff_attendance')
            ->whereDate('date', $validated['date'])
            ->whereIn('user_id', collect($validated['attendance'])->pluck('user_id'))
            ->get()->keyBy('user_id');

        foreach ($validated['attendance'] as $item) {
            $row = $existing[$item['user_id']] ?? null;

            // A self / biometric check-in keeps its times and method unless the admin changes the status.
            if ($row && $row->method !== 'manual' && $row->status === $item['status']) {
                if (!empty($item['remarks'])) {
                    DB::table('staff_attendance')->where('id', $row->id)->update(['remarks' => $item['remarks'], 'updated_at' => now()]);
                }
                $count++;
                continue;
            }

            $data = [
                'school_id' => $schoolId ?? DB::table('users')->where('id', $item['user_id'])->value('school_id'),
                'status' => $item['status'],
                'method' => 'manual',
                'marked_by' => $markedBy,
                'updated_at' => now(),
            ];
            if (!empty($item['remarks'])) {
                $data['remarks'] = $item['remarks'];
            }
            if ($row) {
                if ($row->method !== 'manual') {
                    $changed++;
                    $data['remarks'] = trim(($item['remarks'] ?? '') . ' [Overridden by admin; was ' . $row->status . ' via ' . $row->method . ']');
                }
                DB::table('staff_attendance')->where('id', $row->id)->update($data);
            } else {
                DB::table('staff_attendance')->insert($data + [
                    'user_id' => $item['user_id'],
                    'date' => $validated['date'],
                    'created_at' => now(),
                ]);
            }
            $count++;
        }

        $msg = "Staff attendance saved for {$count} members";
        if ($changed) {
            $msg .= " ({$changed} self/fingerprint check-ins overridden)";
        }
        return back()->with('success', $msg);
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
