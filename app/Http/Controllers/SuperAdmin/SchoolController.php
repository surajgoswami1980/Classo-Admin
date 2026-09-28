<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'board']);

        $schools = School::query()
            ->when(!empty($filters['search']), fn($q) => $q->where(function ($q2) use ($filters) {
                $q2->where('name', 'like', "%{$filters['search']}%")
                   ->orWhere('code', 'like', "%{$filters['search']}%")
                   ->orWhere('email', 'like', "%{$filters['search']}%");
            }))
            ->when(isset($filters['status']), fn($q) => $q->where('is_active', $filters['status'] === 'active'))
            ->when(!empty($filters['board']), fn($q) => $q->where('board_affiliation', $filters['board']))
            ->latest()
            ->paginate(20)
            ->appends($filters);

        return view('super-admin.schools.index', compact('schools', 'filters'));
    }

    public function create()
    {
        return view('super-admin.schools.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:schools,code',
            'board_affiliation' => 'required|in:CBSE,ICSE,STATE,UNIVERSITY',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'max_students' => 'nullable|integer|min:10|max:99999',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:users,email',
            'admin_phone' => 'nullable|string|max:15',
            'admin_password' => 'required|string|min:8',
        ], [
            'code.unique' => 'This school code is already taken. Choose a different one.',
            'code.alpha_num' => 'School code can only contain letters and numbers.',
            'admin_email.unique' => 'This email is already registered in the system.',
            'admin_password.min' => 'Password must be at least 8 characters.',
        ]);

        DB::beginTransaction();
        try {
            $school = School::create([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'board_affiliation' => $validated['board_affiliation'],
                'email' => $validated['contact_email'] ?? '',
                'phone' => $validated['contact_phone'] ?? '',
                'address' => $validated['address'] ?? '',
                'city' => $validated['city'] ?? '',
                'state' => $validated['state'] ?? '',
                'pincode' => $validated['pincode'] ?? '',
                'subscription_plan' => 'trial',
                'subscription_start' => now(),
                'subscription_end' => now()->addDays(30),
                'max_students' => $validated['max_students'] ?? 300,
                'max_staff' => 50,
                'is_active' => true,
            ]);

            $user = User::create([
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'phone' => $validated['admin_phone'] ?? null,
                'password' => Hash::make($validated['admin_password']),
                'school_id' => $school->id,
                'is_active' => true,
            ]);

            // The school-admin role is granted every module permission at seed
            // time and bypasses all permission gates — so a freshly onboarded
            // client has full access to every module out of the box and can
            // then carve out narrower policies/roles for their own sub-admins
            // and incharges via Team Management.
            $user->assignRole('school-admin');

            DB::commit();

            return redirect()->route('admin.schools.index')
                ->with('success', "School '{$school->name}' onboarded successfully! Code: {$school->code}");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to onboard school: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(School $school)
    {
        $school->load('users');
        $adminUser = $school->users()->whereHas('roles', fn($q) => $q->where('name', 'school-admin'))->first();
        $stats = [
            'total_users' => $school->users()->count(),
            'teachers' => $school->users()->role('teacher')->count(),
        ];
        return view('super-admin.schools.show', compact('school', 'adminUser', 'stats'));
    }

    public function edit(School $school)
    {
        return view('super-admin.schools.edit', compact('school'));
    }

    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'board_affiliation' => 'required|in:CBSE,ICSE,STATE,UNIVERSITY',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'max_students' => 'nullable|integer|min:10',
            'subscription_plan' => 'nullable|string|in:trial,starter,growth,enterprise',
        ]);

        $school->update($validated);

        return redirect()->route('admin.schools.index')->with('success', 'School updated successfully');
    }

    public function destroy(School $school)
    {
        $school->update(['is_active' => false]);
        return redirect()->route('admin.schools.index')->with('success', 'School deactivated');
    }

    public function activate(School $school)
    {
        $school->update(['is_active' => true]);
        return back()->with('success', "{$school->name} activated successfully");
    }

    public function deactivate(School $school)
    {
        $school->update(['is_active' => false]);
        return back()->with('success', "{$school->name} deactivated");
    }
}
