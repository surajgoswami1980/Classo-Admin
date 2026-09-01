<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicController extends Controller
{
    private function getSchoolId(): ?int
    {
        // current_school_id() is impersonation-aware: while a super-admin is
        // switched into a school, this scopes to that school; otherwise
        // (no impersonation, own school_id null) it stays null -> global view.
        return current_school_id();
    }

    // ─── Academic Sessions ────────────────────────────────────

    public function sessions(Request $request)
    {
        $schoolId = $this->getSchoolId() ?? $request->get('school_id');
        $sessions = DB::table('academic_sessions')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->get();

        return view('admin.academic.sessions', compact('sessions'));
    }

    public function storeSession(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'nullable|boolean',
            'school_id' => 'nullable|integer|exists:schools,id',
        ]);

        $user = auth()->user();
        $schoolId = $user->hasRole('super-admin')
            ? ($validated['school_id'] ?? DB::table('schools')->where('is_active', true)->value('id'))
            : $user->school_id;

        if (!$schoolId) return back()->withErrors(['error' => 'No school found. Please onboard a school first.'])->withInput();

        // If marking as current, unmark others
        if (!empty($validated['is_current'])) {
            DB::table('academic_sessions')->where('school_id', $schoolId)->update(['is_current' => 0]);
        }

        DB::table('academic_sessions')->insert([
            'school_id' => $schoolId,
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_current' => $validated['is_current'] ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Academic session created');
    }

    // ─── Classes ─────────────────────────────────────────────

    public function classes(Request $request)
    {
        $schoolId = $this->getSchoolId() ?? $request->get('school_id');
        $classes = DB::table('classes')
            ->leftJoin('academic_sessions', 'classes.academic_session_id', '=', 'academic_sessions.id')
            ->select(['classes.*', 'academic_sessions.name as session_name'])
            ->when($schoolId, fn($q) => $q->where('classes.school_id', $schoolId))
            ->orderBy('classes.numeric_order')
            ->get();

        $sessions = DB::table('academic_sessions')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->where('is_current', 1)
            ->pluck('name', 'id');

        return view('admin.academic.classes', compact('classes', 'sessions'));
    }

    public function storeClass(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'academic_session_id' => 'required|integer|exists:academic_sessions,id',
            'numeric_order' => 'nullable|integer|min:0',
            'school_id' => 'nullable|integer|exists:schools,id',
        ]);

        $user = auth()->user();
        $schoolId = $user->hasRole('super-admin')
            ? ($validated['school_id'] ?? DB::table('academic_sessions')->where('id', $validated['academic_session_id'])->value('school_id'))
            : $user->school_id;

        DB::table('classes')->insert([
            'school_id' => $schoolId,
            'academic_session_id' => $validated['academic_session_id'],
            'name' => $validated['name'],
            'numeric_order' => $validated['numeric_order'] ?? 0,
            'created_at' => now(),
        ]);

        return back()->with('success', "Class '{$validated['name']}' created");
    }

    // ─── Sections ────────────────────────────────────────────

    public function sections(Request $request)
    {
        $schoolId = $this->getSchoolId() ?? $request->get('school_id');
        $sections = DB::table('sections')
            ->join('classes', 'sections.class_id', '=', 'classes.id')
            ->select(['sections.*', 'classes.name as class_name'])
            ->when($schoolId, fn($q) => $q->where('sections.school_id', $schoolId))
            ->orderBy('classes.numeric_order')
            ->orderBy('sections.name')
            ->get();

        $classes = DB::table('classes')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->pluck('name', 'id');

        return view('admin.academic.sections', compact('sections', 'classes'));
    }

    public function storeSection(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:10',
            'class_id' => 'required|integer|exists:classes,id',
            'capacity' => 'nullable|integer|min:1|max:200',
            'school_id' => 'nullable|integer|exists:schools,id',
        ]);

        $user = auth()->user();
        $schoolId = $user->hasRole('super-admin')
            ? ($validated['school_id'] ?? DB::table('classes')->where('id', $validated['class_id'])->value('school_id'))
            : $user->school_id;

        DB::table('sections')->insert([
            'school_id' => $schoolId,
            'class_id' => $validated['class_id'],
            'name' => strtoupper($validated['name']),
            'capacity' => $validated['capacity'] ?? 40,
            'created_at' => now(),
        ]);

        return back()->with('success', "Section '{$validated['name']}' created");
    }

    public function destroySection($id)
    {
        DB::table('sections')->where('id', $id)->delete();
        return back()->with('success', 'Section deleted');
    }

    public function destroyClass($id)
    {
        // Check if has students
        $hasStudents = DB::table('students')->where('class_id', $id)->exists();
        if ($hasStudents) {
            return back()->withErrors(['error' => 'Cannot delete class with active students']);
        }
        DB::table('sections')->where('class_id', $id)->delete();
        DB::table('classes')->where('id', $id)->delete();
        return back()->with('success', 'Class deleted');
    }
}
