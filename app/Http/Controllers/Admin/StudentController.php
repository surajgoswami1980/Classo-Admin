<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    private function getSchoolId(Request $request): ?int
    {
        // current_school_id() handles impersonation: switched-in super-admin
        // and real school users both get scoped to one school. A true
        // global super-admin (no impersonation) can still optionally filter
        // by ?school_id= without switching.
        return current_school_id() ?? $request->get('school_id');
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'school_id', 'status', 'per_page']);
        $schoolId = $this->getSchoolId($request);

        $students = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('classes', 'students.class_id', '=', 'classes.id')
            ->leftJoin('sections', 'students.section_id', '=', 'sections.id')
            ->select([
                'students.*',
                'users.name as user_name',
                'users.email',
                'users.phone',
                'classes.name as class_name',
                'sections.name as section_name',
            ])
            ->when($schoolId, fn($q) => $q->where('students.school_id', $schoolId))
            ->when(!empty($filters['search']), fn($q) => $q->where(function ($q2) use ($filters) {
                $q2->where('users.name', 'like', "%{$filters['search']}%")
                   ->orWhere('students.admission_number', 'like', "%{$filters['search']}%")
                   ->orWhere('students.roll_number', 'like', "%{$filters['search']}%")
                   ->orWhere('users.phone', 'like', "%{$filters['search']}%");
            }))
            ->when(!empty($filters['status']), fn($q) => $q->where('students.status', $filters['status']))
            ->orderBy('students.created_at', 'desc')
            ->paginate($filters['per_page'] ?? 20)
            ->appends($filters);

        // Get classes for filter dropdown
        $classes = DB::table('classes')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->pluck('name', 'id');

        $schools = (auth()->user()->hasRole('super-admin') && !is_impersonating()) ? School::active()->pluck('name', 'id') : collect();

        return view('admin.students.index', compact('students', 'filters', 'classes', 'schools'));
    }

    public function create()
    {
        $user = auth()->user();
        $schoolId = current_school_id();

        // For a global super-admin (no school selected), show all
        // classes/sections; a switched-in super-admin or real school user
        // only sees their one school's.
        $classes = DB::table('classes')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->pluck('name', 'id');

        $sections = DB::table('sections')
            ->join('classes', 'sections.class_id', '=', 'classes.id')
            ->when($schoolId, fn($q) => $q->where('sections.school_id', $schoolId))
            ->select(['sections.id', 'sections.name', 'sections.class_id', 'classes.name as class_name'])
            ->get();

        $schools = ($user->hasRole('super-admin') && !is_impersonating()) ? \App\Models\School::where('is_active', true)->pluck('name', 'id') : collect();

        return view('admin.students.create', compact('classes', 'sections', 'schools'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:15',
            'password' => 'nullable|string|min:6',
            'school_id' => 'nullable|integer|exists:schools,id',
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'admission_number' => 'nullable|string|max:50',
            'roll_number' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'blood_group' => 'nullable|string|max:5',
            'father_name' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:15',
            'mother_name' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
        ], [
            'first_name.required' => 'Student first name is required.',
            'class_id.required' => 'Please select a class.',
            'section_id.required' => 'Please select a section.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
        ]);

        $user = auth()->user();
        $schoolId = current_school_id()
            ?? ($validated['school_id'] ?? DB::table('classes')->where('id', $validated['class_id'])->value('school_id'));

        if (!$schoolId) {
            return back()->withErrors(['school_id' => 'School is required.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Create user account for the student (password = phone number or default)
            $defaultPassword = $validated['phone'] ?? $validated['password'] ?? 'Student@123';
            $studentUser = User::create([
                'name' => trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')),
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($defaultPassword),
                'school_id' => $schoolId,
                'is_active' => true,
            ]);
            $studentUser->assignRole('student');

            // Create student record
            DB::table('students')->insert([
                'school_id' => $schoolId,
                'user_id' => $studentUser->id,
                'class_id' => $validated['class_id'],
                'section_id' => $validated['section_id'],
                'admission_number' => $validated['admission_number'] ?? null,
                'roll_number' => $validated['roll_number'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'blood_group' => $validated['blood_group'] ?? null,
                'father_name' => $validated['father_name'] ?? null,
                'father_phone' => $validated['father_phone'] ?? null,
                'mother_name' => $validated['mother_name'] ?? null,
                'mother_phone' => $validated['mother_phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'pincode' => $validated['pincode'] ?? null,
                'status' => 'active',
                'admission_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.students.index')->with('success', 'Student added successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to add student: ' . $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $student = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('classes', 'students.class_id', '=', 'classes.id')
            ->leftJoin('sections', 'students.section_id', '=', 'sections.id')
            ->select(['students.*', 'users.name as user_name', 'users.email', 'users.phone', 'classes.name as class_name', 'sections.name as section_name'])
            ->where('students.id', $id)
            ->first();

        if (!$student) abort(404, 'Student not found');

        return view('admin.students.show', compact('student'));
    }

    public function edit($id)
    {
        $student = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->select(['students.*', 'users.name as user_name', 'users.email', 'users.phone'])
            ->where('students.id', $id)
            ->first();

        if (!$student) abort(404, 'Student not found');

        // Always use the student's own school (not the viewer's context) —
        // correct regardless of whether a super-admin is impersonating.
        $schoolId = $student->school_id;

        $classes = DB::table('classes')->where('school_id', $schoolId)->pluck('name', 'id');
        $sections = DB::table('sections')->where('school_id', $schoolId)->get(['id', 'name', 'class_id']);

        return view('admin.students.edit', compact('student', 'classes', 'sections'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:15',
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'admission_number' => 'nullable|string|max:50',
            'roll_number' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'blood_group' => 'nullable|string|max:5',
            'father_name' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:15',
            'mother_name' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'status' => 'nullable|in:active,inactive,transferred,graduated',
        ]);

        $student = DB::table('students')->where('id', $id)->first();
        if (!$student) abort(404);

        DB::beginTransaction();
        try {
            // Update user record
            User::where('id', $student->user_id)->update([
                'name' => trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? '')),
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ]);

            // Update student record
            DB::table('students')->where('id', $id)->update([
                'class_id' => $validated['class_id'],
                'section_id' => $validated['section_id'],
                'admission_number' => $validated['admission_number'] ?? null,
                'roll_number' => $validated['roll_number'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'blood_group' => $validated['blood_group'] ?? null,
                'father_name' => $validated['father_name'] ?? null,
                'father_phone' => $validated['father_phone'] ?? null,
                'mother_name' => $validated['mother_name'] ?? null,
                'mother_phone' => $validated['mother_phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'pincode' => $validated['pincode'] ?? null,
                'status' => $validated['status'] ?? 'active',
                'updated_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('admin.students.index')->with('success', 'Student updated successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        $student = DB::table('students')->where('id', $id)->first();
        if (!$student) abort(404);

        DB::table('students')->where('id', $id)->update(['status' => 'inactive', 'updated_at' => now()]);

        return redirect()->route('admin.students.index')->with('success', 'Student deactivated');
    }

    public function import(Request $request)
    {
        // TODO: CSV import
        return back()->with('info', 'Import feature coming soon');
    }
}
