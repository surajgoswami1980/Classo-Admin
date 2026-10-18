<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Admin-side assignment management. Reads/writes the same tables the API/app
 * use (assignments, assignment_submissions) so teacher app + admin stay in
 * sync. Admin can create assignments, view who submitted, and award marks.
 */
class AssignmentController extends Controller
{
    private function schoolId(): ?int
    {
        return current_school_id();
    }

    public function index(Request $request)
    {
        $schoolId = $this->schoolId();

        $assignments = DB::table('assignments as a')
            ->leftJoin('classes as c', 'c.id', '=', 'a.class_id')
            ->leftJoin('sections as s', 's.id', '=', 'a.section_id')
            ->leftJoin('subjects as sub', 'sub.id', '=', 'a.subject_id')
            ->when($schoolId, fn ($q) => $q->where('a.school_id', $schoolId))
            ->select([
                'a.*',
                'c.name as class_name', 's.name as section_name', 'sub.name as subject_name',
            ])
            ->orderByDesc('a.created_at')
            ->paginate(15);

        // submission progress per assignment
        $ids = collect($assignments->items())->pluck('id');
        $counts = DB::table('assignment_submissions')
            ->whereIn('assignment_id', $ids)
            ->select('assignment_id', 'status', DB::raw('COUNT(*) as c'))
            ->groupBy('assignment_id', 'status')
            ->get()
            ->groupBy('assignment_id');

        $classes = DB::table('classes')->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->orderBy('numeric_order')->get(['id', 'name']);
        $sections = DB::table('sections')->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->orderBy('name')->get(['id', 'name', 'class_id']);
        $subjects = DB::table('subjects')->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->orderBy('name')->get(['id', 'name', 'class_id']);

        return view('admin.assignments.index', compact('assignments', 'counts', 'classes', 'sections', 'subjects'));
    }

    public function store(Request $request)
    {
        $schoolId = $this->schoolId();
        abort_unless($schoolId, 422, 'Select a school first.');

        $v = $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'title' => 'required|string|max:255',
            'topic' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'due_date' => 'required|date',
            'max_marks' => 'nullable|integer|min:1|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,doc,docx|max:8192',
        ]);

        $attachmentUrl = null;
        $attachmentType = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentUrl = $file->store("assignments/{$schoolId}", 'public');
            $ext = strtolower($file->getClientOriginalExtension());
            $attachmentType = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']) ? 'image' : ($ext === 'pdf' ? 'pdf' : 'doc');
        }

        // teacher_id: resolve the logged-in user's teacher record if any, else 0 (admin-created)
        $teacherId = (int) (DB::table('teachers')->where('user_id', auth()->id())->value('id') ?? 0);

        DB::table('assignments')->insert([
            'school_id' => $schoolId,
            'teacher_id' => $teacherId,
            'class_id' => $v['class_id'],
            'section_id' => $v['section_id'],
            'subject_id' => $v['subject_id'],
            'title' => $v['title'],
            'topic' => $v['topic'] ?? null,
            'description' => $v['description'] ?? null,
            'due_date' => $v['due_date'],
            'max_marks' => $v['max_marks'] ?? 100,
            'attachment_url' => $attachmentUrl,
            'attachment_type' => $attachmentType,
            'status' => 'published',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Assignment created.');
    }

    public function destroy($id)
    {
        $schoolId = $this->schoolId();
        $a = DB::table('assignments')->where('id', $id)->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->first();
        abort_unless($a, 404);
        DB::table('assignment_submissions')->where('assignment_id', $id)->delete();
        DB::table('assignments')->where('id', $id)->delete();

        return back()->with('success', 'Assignment deleted.');
    }

    /** Grading screen: every student in the class/section merged with their submission. */
    public function submissions($id)
    {
        $schoolId = $this->schoolId();
        $assignment = DB::table('assignments as a')
            ->leftJoin('classes as c', 'c.id', '=', 'a.class_id')
            ->leftJoin('sections as s', 's.id', '=', 'a.section_id')
            ->leftJoin('subjects as sub', 'sub.id', '=', 'a.subject_id')
            ->where('a.id', $id)
            ->when($schoolId, fn ($q) => $q->where('a.school_id', $schoolId))
            ->select(['a.*', 'c.name as class_name', 's.name as section_name', 'sub.name as subject_name'])
            ->first();
        abort_unless($assignment, 404);

        // Auto-mark missed for past-due non-submitters.
        if (strtotime($assignment->due_date) < time()) {
            DB::statement(
                "INSERT INTO assignment_submissions (school_id, assignment_id, student_id, status, marks, created_at, updated_at)
                 SELECT ?, ?, st.id, 'missed', 0, NOW(), NOW()
                 FROM students st
                 WHERE st.school_id = ? AND st.class_id = ? AND st.section_id = ? AND st.status = 'active'
                   AND NOT EXISTS (SELECT 1 FROM assignment_submissions s WHERE s.assignment_id = ? AND s.student_id = st.id)",
                [$assignment->school_id, $assignment->id, $assignment->school_id, $assignment->class_id, $assignment->section_id, $assignment->id]
            );
        }

        $rows = DB::table('students as st')
            ->join('users as u', 'u.id', '=', 'st.user_id')
            ->leftJoin('assignment_submissions as sub', function ($j) use ($id) {
                $j->on('sub.student_id', '=', 'st.id')->where('sub.assignment_id', '=', $id);
            })
            ->where('st.school_id', $assignment->school_id)
            ->where('st.class_id', $assignment->class_id)
            ->where('st.section_id', $assignment->section_id)
            ->where('st.status', 'active')
            ->orderBy('st.roll_number')
            ->select([
                'st.id as student_id', 'u.name as student_name', 'st.roll_number',
                'sub.id as submission_id', 'sub.status', 'sub.description', 'sub.attachment_url',
                'sub.attachment_type', 'sub.submitted_at', 'sub.is_late', 'sub.marks', 'sub.feedback',
            ])
            ->get();

        return view('admin.assignments.submissions', compact('assignment', 'rows'));
    }

    public function grade(Request $request, $id)
    {
        $schoolId = $this->schoolId();
        $assignment = DB::table('assignments')->where('id', $id)->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->first();
        abort_unless($assignment, 404);

        $v = $request->validate([
            'marks' => 'required|array',
            'marks.*' => 'nullable|numeric|min:0|max:' . $assignment->max_marks,
            'feedback' => 'nullable|array',
        ]);

        foreach ($v['marks'] as $studentId => $mark) {
            if ($mark === null || $mark === '') {
                continue;
            }
            $feedback = $request->input("feedback.$studentId");
            $existing = DB::table('assignment_submissions')->where('assignment_id', $id)->where('student_id', $studentId)->first();
            if ($existing) {
                DB::table('assignment_submissions')->where('id', $existing->id)->update([
                    'marks' => $mark, 'feedback' => $feedback, 'status' => 'graded',
                    'graded_by' => auth()->id(), 'graded_at' => now(), 'updated_at' => now(),
                ]);
            } else {
                DB::table('assignment_submissions')->insert([
                    'school_id' => $assignment->school_id, 'assignment_id' => $id, 'student_id' => $studentId,
                    'marks' => $mark, 'feedback' => $feedback, 'status' => 'graded',
                    'graded_by' => auth()->id(), 'graded_at' => now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        return back()->with('success', 'Marks saved.');
    }
}
