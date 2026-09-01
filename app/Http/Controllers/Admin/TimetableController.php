<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    private function getSchoolId(): ?int
    {
        // current_school_id() is impersonation-aware: while a super-admin is
        // switched into a school, this scopes to that school; otherwise
        // (no impersonation, own school_id null) it stays null -> global view.
        return current_school_id();
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();
        $filters = $request->only(['class_id', 'section_id']);

        $classes = DB::table('classes')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('numeric_order')
            ->pluck('name', 'id');

        $sections = DB::table('sections')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->get(['id', 'name', 'class_id']);

        $timetable = collect();
        if (!empty($filters['class_id']) && !empty($filters['section_id'])) {
            $timetable = DB::table('timetable_periods')
                ->join('subjects', 'timetable_periods.subject_id', '=', 'subjects.id')
                ->join('users', 'timetable_periods.teacher_id', '=', 'users.id')
                ->where('timetable_periods.class_id', $filters['class_id'])
                ->where('timetable_periods.section_id', $filters['section_id'])
                ->when($schoolId, fn($q) => $q->where('timetable_periods.school_id', $schoolId))
                ->select([
                    'timetable_periods.*',
                    'subjects.name as subject_name',
                    'users.name as teacher_name',
                ])
                ->orderBy('timetable_periods.day_of_week')
                ->orderBy('timetable_periods.period_number')
                ->get();
        }

        $subjects = DB::table('subjects')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->when(!empty($filters['class_id']), fn($q) => $q->where('class_id', $filters['class_id']))
            ->pluck('name', 'id');

        $teachers = DB::table('teachers')
            ->join('users', 'teachers.user_id', '=', 'users.id')
            ->when($schoolId, fn($q) => $q->where('teachers.school_id', $schoolId))
            ->where('teachers.status', 'active')
            ->pluck('users.name', 'users.id');

        $sessions = DB::table('academic_sessions')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->where('is_current', true)
            ->pluck('name', 'id');

        return view('admin.timetable.index', compact('filters', 'classes', 'sections', 'timetable', 'subjects', 'teachers', 'sessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'teacher_id' => 'required|integer|exists:users,id',
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'period_number' => 'required|integer|min:1|max:12',
            'academic_session_id' => 'required|integer|exists:academic_sessions,id',
        ]);

        $schoolId = $this->getSchoolId()
            ?? DB::table('classes')->where('id', $validated['class_id'])->value('school_id');

        // Check for conflict (same class/section/day/period)
        $conflict = DB::table('timetable_periods')
            ->where('school_id', $schoolId)
            ->where('class_id', $validated['class_id'])
            ->where('section_id', $validated['section_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where('period_number', $validated['period_number'])
            ->exists();

        if ($conflict) {
            return back()->withErrors(['period_number' => 'This period slot is already occupied.'])->withInput();
        }

        // Check teacher conflict (same teacher/day/period)
        $teacherConflict = DB::table('timetable_periods')
            ->where('school_id', $schoolId)
            ->where('teacher_id', $validated['teacher_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where('period_number', $validated['period_number'])
            ->exists();

        if ($teacherConflict) {
            return back()->withErrors(['teacher_id' => 'This teacher already has a class at this time.'])->withInput();
        }

        DB::table('timetable_periods')->insert([
            'school_id' => $schoolId,
            'academic_session_id' => $validated['academic_session_id'],
            'class_id' => $validated['class_id'],
            'section_id' => $validated['section_id'],
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $validated['teacher_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'period_number' => $validated['period_number'],
            'created_at' => now(),
        ]);

        return back()->with('success', 'Period added to timetable');
    }

    public function destroy($id)
    {
        DB::table('timetable_periods')->where('id', $id)->delete();
        return back()->with('success', 'Period removed from timetable');
    }
}
