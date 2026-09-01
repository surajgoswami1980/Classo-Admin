<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
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
        $filters = $request->only(['exam_type', 'session_id']);

        $exams = DB::table('exams')
            ->leftJoin('academic_sessions', 'exams.academic_session_id', '=', 'academic_sessions.id')
            ->select(['exams.*', 'academic_sessions.name as session_name'])
            ->when($schoolId, fn($q) => $q->where('exams.school_id', $schoolId))
            ->when(!empty($filters['exam_type']), fn($q) => $q->where('exams.exam_type', $filters['exam_type']))
            ->orderByDesc('exams.created_at')
            ->paginate(20)
            ->appends($filters);

        $sessions = DB::table('academic_sessions')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->pluck('name', 'id');

        return view('admin.exams.index', compact('exams', 'filters', 'sessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'exam_type' => 'required|in:unit_test,mid_term,final,quarterly,half_yearly',
            'academic_session_id' => 'required|integer|exists:academic_sessions,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [
            'name.required' => 'Exam name is required.',
            'exam_type.required' => 'Please select exam type.',
            'end_date.after_or_equal' => 'End date must be after start date.',
        ]);

        $schoolId = $this->getSchoolId();
        if (!$schoolId) {
            $schoolId = DB::table('academic_sessions')->where('id', $validated['academic_session_id'])->value('school_id');
        }

        DB::table('exams')->insert([
            'school_id' => $schoolId,
            'academic_session_id' => $validated['academic_session_id'],
            'name' => $validated['name'],
            'exam_type' => $validated['exam_type'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_published' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.exams.index')->with('success', "Exam '{$validated['name']}' created");
    }

    public function marks(Request $request, $examId)
    {
        $schoolId = $this->getSchoolId();
        $exam = DB::table('exams')->where('id', $examId)->first();
        if (!$exam) abort(404, 'Exam not found');

        $filters = $request->only(['class_id', 'section_id', 'subject_id']);

        $classes = DB::table('classes')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->pluck('name', 'id');

        $sections = DB::table('sections')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->get(['id', 'name', 'class_id']);

        $subjects = DB::table('subjects')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->when(!empty($filters['class_id']), fn($q) => $q->where('class_id', $filters['class_id']))
            ->pluck('name', 'id');

        $students = collect();
        $existingMarks = collect();

        if (!empty($filters['class_id']) && !empty($filters['section_id'])) {
            $students = DB::table('students')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->where('students.class_id', $filters['class_id'])
                ->where('students.section_id', $filters['section_id'])
                ->where('students.status', 'active')
                ->when($schoolId, fn($q) => $q->where('students.school_id', $schoolId))
                ->select(['students.id', 'students.roll_number', 'users.name'])
                ->orderBy('students.roll_number')
                ->get();

            if (!empty($filters['subject_id'])) {
                $examSubject = DB::table('exam_subjects')
                    ->where('exam_id', $examId)
                    ->where('subject_id', $filters['subject_id'])
                    ->where('class_id', $filters['class_id'])
                    ->first();

                if ($examSubject) {
                    $existingMarks = DB::table('student_marks')
                        ->where('exam_subject_id', $examSubject->id)
                        ->pluck('marks_obtained', 'student_id');
                }
            }
        }

        return view('admin.exams.marks', compact('exam', 'filters', 'classes', 'sections', 'subjects', 'students', 'existingMarks'));
    }

    public function storeMarks(Request $request, $examId)
    {
        $validated = $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'max_marks' => 'required|numeric|min:1',
            'passing_marks' => 'required|numeric|min:0',
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|integer',
            'marks.*.obtained' => 'nullable|numeric|min:0',
        ]);

        $schoolId = $this->getSchoolId() ?? DB::table('exams')->where('id', $examId)->value('school_id');

        // Ensure exam_subject exists
        $examSubject = DB::table('exam_subjects')
            ->where('exam_id', $examId)
            ->where('subject_id', $validated['subject_id'])
            ->where('class_id', $validated['class_id'])
            ->first();

        if (!$examSubject) {
            $examSubjectId = DB::table('exam_subjects')->insertGetId([
                'school_id' => $schoolId,
                'exam_id' => $examId,
                'subject_id' => $validated['subject_id'],
                'class_id' => $validated['class_id'],
                'max_marks' => $validated['max_marks'],
                'passing_marks' => $validated['passing_marks'],
                'created_at' => now(),
            ]);
        } else {
            $examSubjectId = $examSubject->id;
            DB::table('exam_subjects')->where('id', $examSubjectId)->update([
                'max_marks' => $validated['max_marks'],
                'passing_marks' => $validated['passing_marks'],
            ]);
        }

        $count = 0;
        foreach ($validated['marks'] as $item) {
            if ($item['obtained'] === null || $item['obtained'] === '') continue;

            $obtained = (float) $item['obtained'];
            if ($obtained > $validated['max_marks']) continue;

            $percentage = ($obtained / $validated['max_marks']) * 100;
            $grade = $this->calculateGrade($percentage);

            DB::table('student_marks')->updateOrInsert(
                ['student_id' => $item['student_id'], 'exam_subject_id' => $examSubjectId],
                [
                    'school_id' => $schoolId,
                    'exam_id' => $examId,
                    'marks_obtained' => $obtained,
                    'grade' => $grade,
                    'entered_by' => auth()->id(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $count++;
        }

        return back()->with('success', "Marks saved for {$count} students");
    }

    public function results(Request $request, $examId)
    {
        $schoolId = $this->getSchoolId();
        $exam = DB::table('exams')->where('id', $examId)->first();
        if (!$exam) abort(404);

        $results = DB::table('student_marks')
            ->join('students', 'student_marks.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('exam_subjects', 'student_marks.exam_subject_id', '=', 'exam_subjects.id')
            ->join('subjects', 'exam_subjects.subject_id', '=', 'subjects.id')
            ->where('student_marks.exam_id', $examId)
            ->when($schoolId, fn($q) => $q->where('student_marks.school_id', $schoolId))
            ->select([
                'students.id as student_id', 'users.name as student_name', 'students.roll_number',
                'subjects.name as subject_name', 'exam_subjects.max_marks', 'exam_subjects.passing_marks',
                'student_marks.marks_obtained', 'student_marks.grade',
            ])
            ->orderBy('users.name')
            ->orderBy('subjects.name')
            ->get();

        return view('admin.exams.results', compact('exam', 'results'));
    }

    public function reportCard($studentId)
    {
        $student = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('classes', 'students.class_id', '=', 'classes.id')
            ->leftJoin('sections', 'students.section_id', '=', 'sections.id')
            ->select(['students.*', 'users.name', 'classes.name as class_name', 'sections.name as section_name'])
            ->where('students.id', $studentId)
            ->first();

        if (!$student) abort(404);

        $exams = DB::table('exams')->where('school_id', $student->school_id)->where('is_published', 1)->get();

        $marks = DB::table('student_marks')
            ->join('exam_subjects', 'student_marks.exam_subject_id', '=', 'exam_subjects.id')
            ->join('subjects', 'exam_subjects.subject_id', '=', 'subjects.id')
            ->join('exams', 'student_marks.exam_id', '=', 'exams.id')
            ->where('student_marks.student_id', $studentId)
            ->select(['exams.name as exam_name', 'subjects.name as subject_name', 'exam_subjects.max_marks', 'student_marks.marks_obtained', 'student_marks.grade'])
            ->get();

        return view('admin.exams.report-card', compact('student', 'exams', 'marks'));
    }

    private function calculateGrade(float $percentage): string
    {
        if ($percentage >= 91) return 'A+';
        if ($percentage >= 81) return 'A';
        if ($percentage >= 71) return 'B+';
        if ($percentage >= 61) return 'B';
        if ($percentage >= 51) return 'C+';
        if ($percentage >= 41) return 'C';
        if ($percentage >= 33) return 'D';
        return 'F';
    }
}
