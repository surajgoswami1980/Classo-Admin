<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
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
        $filters = $request->only(['search', 'status', 'department']);
        $schoolId = $this->getSchoolId($request);

        $teachers = DB::table('teachers')
            ->join('users', 'teachers.user_id', '=', 'users.id')
            ->select([
                'teachers.*',
                'users.name as user_name',
                'users.email',
                'users.phone',
                'users.employee_id',
            ])
            ->when($schoolId, fn($q) => $q->where('teachers.school_id', $schoolId))
            ->when(!empty($filters['search']), fn($q) => $q->where(function ($q2) use ($filters) {
                $q2->where('users.name', 'like', "%{$filters['search']}%")
                   ->orWhere('users.email', 'like', "%{$filters['search']}%")
                   ->orWhere('users.employee_id', 'like', "%{$filters['search']}%");
            }))
            ->when(!empty($filters['status']), fn($q) => $q->where('teachers.status', $filters['status']))
            ->when(!empty($filters['department']), fn($q) => $q->where('teachers.department', $filters['department']))
            ->orderBy('teachers.created_at', 'desc')
            ->paginate(20)
            ->appends($filters);

        return view('admin.teachers.index', compact('teachers', 'filters'));
    }

    public function create()
    {
        $user = auth()->user();
        $schools = ($user->hasRole('super-admin') && !is_impersonating()) ? School::active()->pluck('name', 'id') : collect();

        $schoolId = current_school_id();
        $classes = DB::table('classes')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('numeric_order')
            ->get(['id', 'name']);
        $sections = DB::table('sections')
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->orderBy('name')
            ->get(['id', 'name', 'class_id']);
        // Subjects across every class (Nursery, LKG, UKG, 1st …) so a teacher
        // can be assigned subjects spanning multiple levels.
        $subjects = DB::table('subjects')
            ->join('classes', 'subjects.class_id', '=', 'classes.id')
            ->when($schoolId, fn($q) => $q->where('subjects.school_id', $schoolId))
            ->orderBy('classes.numeric_order')
            ->orderBy('subjects.name')
            ->get(['subjects.id', 'subjects.name', 'subjects.class_id', 'classes.name as class_name']);

        return view('admin.teachers.create', compact('schools', 'classes', 'sections', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:15',
            'password' => 'nullable|string|min:8',
            'employee_id' => 'nullable|string|max:50',
            'school_id' => 'nullable|integer|exists:schools,id',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'qualifications' => 'nullable|string|max:1000',
            'qualification' => 'nullable|string|max:1000',
            'date_of_joining' => 'nullable|date',
            'joining_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            // Class assignment & subjects
            'is_class_incharge' => 'nullable|boolean',
            'incharge_class_id' => 'nullable|integer|exists:classes,id',
            'incharge_section_id' => 'nullable|integer|exists:sections,id',
            'assigned_classes' => 'nullable|array',
            'assigned_classes.*' => 'integer|exists:classes,id',
            'assigned_subjects' => 'nullable|array',
            'assigned_subjects.*' => 'integer|exists:subjects,id',
        ], [
            'name.required' => 'Teacher name is required.',
            'email.required' => 'Email is required for teacher login.',
            'email.unique' => 'This email is already registered.',
            'password.min' => 'Password must be at least 8 characters.',
        ]);

        $schoolId = current_school_id() ?? ($validated['school_id'] ?? null);

        if (!$schoolId) {
            return back()->withErrors(['school_id' => 'School is required.'])->withInput();
        }

        DB::beginTransaction();
        try {
            $isIncharge = $request->boolean('is_class_incharge');

            $teacherUser = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => $validated['password'] ?? 'Teacher@123',
                'school_id' => $schoolId,
                'employee_id' => $validated['employee_id'] ?? null,
                'is_active' => true,
                'is_class_incharge' => $isIncharge,
                'incharge_class_id' => $isIncharge ? ($validated['incharge_class_id'] ?? null) : null,
                'incharge_section_id' => $isIncharge ? ($validated['incharge_section_id'] ?? null) : null,
            ]);
            $teacherUser->assignRole('teacher');

            DB::table('teachers')->insert([
                'school_id' => $schoolId,
                'user_id' => $teacherUser->id,
                'designation' => $validated['designation'] ?? null,
                'department' => $validated['department'] ?? null,
                'qualifications' => $validated['qualifications'] ?? ($validated['qualification'] ?? null),
                'date_of_joining' => $validated['date_of_joining'] ?? ($validated['joining_date'] ?? now()->toDateString()),
                'salary' => $validated['salary'] ?? null,
                'assigned_classes' => !empty($validated['assigned_classes']) ? json_encode(array_values($validated['assigned_classes'])) : null,
                'assigned_subjects' => !empty($validated['assigned_subjects']) ? json_encode(array_values($validated['assigned_subjects'])) : null,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('admin.teachers.index')->with('success', 'Teacher added successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to add teacher: ' . $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $teacher = DB::table('teachers')
            ->join('users', 'teachers.user_id', '=', 'users.id')
            ->select(['teachers.*', 'users.name as user_name', 'users.email', 'users.phone', 'users.employee_id'])
            ->where('teachers.id', $id)
            ->first();

        if (!$teacher) abort(404, 'Teacher not found');

        return view('admin.teachers.show', compact('teacher'));
    }

    public function edit($id)
    {
        $teacher = DB::table('teachers')
            ->join('users', 'teachers.user_id', '=', 'users.id')
            ->select(['teachers.*', 'users.name as user_name', 'users.email', 'users.phone', 'users.employee_id'])
            ->where('teachers.id', $id)
            ->first();

        if (!$teacher) abort(404, 'Teacher not found');

        return view('admin.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, $id)
    {
        $teacher = DB::table('teachers')->where('id', $id)->first();
        if (!$teacher) abort(404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$teacher->user_id}",
            'phone' => 'nullable|string|max:15',
            'employee_id' => 'nullable|string|max:50',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'qualifications' => 'nullable|string|max:1000',
            'date_of_joining' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive,resigned',
        ]);

        DB::beginTransaction();
        try {
            User::where('id', $teacher->user_id)->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'employee_id' => $validated['employee_id'] ?? null,
            ]);

            DB::table('teachers')->where('id', $id)->update([
                'designation' => $validated['designation'] ?? null,
                'department' => $validated['department'] ?? null,
                'qualifications' => $validated['qualifications'] ?? null,
                'date_of_joining' => $validated['date_of_joining'] ?? null,
                'salary' => $validated['salary'] ?? null,
                'status' => $validated['status'] ?? 'active',
                'updated_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('admin.teachers.index')->with('success', 'Teacher updated successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        DB::table('teachers')->where('id', $id)->update(['status' => 'inactive', 'updated_at' => now()]);
        return redirect()->route('admin.teachers.index')->with('success', 'Teacher deactivated');
    }

    public function assignClassIncharge(Request $request, $id)
    {
        $validated = $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
        ]);

        $teacher = DB::table('teachers')->where('id', $id)->first();
        if (!$teacher) abort(404);

        User::where('id', $teacher->user_id)->update([
            'is_class_incharge' => true,
            'incharge_class_id' => $validated['class_id'],
            'incharge_section_id' => $validated['section_id'],
        ]);

        return back()->with('success', 'Class incharge assigned');
    }

    public function removeClassIncharge($teacherId, $classId)
    {
        $teacher = DB::table('teachers')->where('id', $teacherId)->first();
        if ($teacher) {
            User::where('id', $teacher->user_id)->update([
                'is_class_incharge' => false,
                'incharge_class_id' => null,
                'incharge_section_id' => null,
            ]);
        }
        return back()->with('success', 'Class incharge removed');
    }
}
