<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Policy;
use App\Models\Admin\Resource;
use App\Models\Admin\Role;
use App\Models\Admin\UserDirectPermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Team management: policies (bundles of resource permissions), roles
 * (bundles of policies), and team-member users — all scoped to the
 * current school (current_school_id(), which also accounts for a
 * super-admin viewing a school via impersonation).
 */
class ManageTeamsController extends Controller
{
    private function schoolId(): int
    {
        $schoolId = current_school_id();
        abort_unless($schoolId, 404, 'Select a school to manage its team.');

        return $schoolId;
    }

    public function index(Request $request)
    {
        if (!current_school_id()) {
            return redirect()->route('admin.schools.index')
                ->with('error', 'Select a school (use "Switch School") to manage its team.');
        }

        $schoolId = $this->schoolId();

        $resources = Resource::with(['actions.permissions' => function ($q) {
            $q->where('status', true);
        }])->where('status', true)->orderBy('name')->get();

        $policies = Policy::where('school_id', $schoolId)
            ->where('is_deleted', false)
            ->withCount('resourcePermissions')
            ->orderBy('name')
            ->get();

        $roles = Role::where('school_id', $schoolId)
            ->where('is_deleted', false)
            ->withCount(['policies', 'users'])
            ->orderBy('name')
            ->get();

        $users = User::where('school_id', $schoolId)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['sub-admin', 'incharge']))
            ->with(['accessRole', 'directPolicies'])
            ->orderBy('name')
            ->paginate(20, ['*'], 'users_page');

        return view('admin.team.index', compact('resources', 'policies', 'roles', 'users'));
    }

    // ─── Policies ────────────────────────────────────────────────────────

    public function storePolicy(Request $request)
    {
        $schoolId = $this->schoolId();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'resource_permission_ids' => 'array',
            'resource_permission_ids.*' => 'integer|exists:resource_permissions,id',
        ]);

        $policy = Policy::create([
            'school_id' => $schoolId,
            'created_by' => auth()->id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $policy->resourcePermissions()->sync($validated['resource_permission_ids'] ?? []);

        return back()->with('success', 'Policy created.');
    }

    public function updatePolicy(Request $request, Policy $policy)
    {
        abort_unless($policy->school_id == $this->schoolId(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'resource_permission_ids' => 'array',
            'resource_permission_ids.*' => 'integer|exists:resource_permissions,id',
        ]);

        $policy->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $policy->resourcePermissions()->sync($validated['resource_permission_ids'] ?? []);

        $this->invalidateForPolicy($policy);

        return back()->with('success', 'Policy updated.');
    }

    public function togglePolicyStatus(Policy $policy)
    {
        abort_unless($policy->school_id == $this->schoolId(), 403);

        $policy->update(['status' => !$policy->status]);
        $this->invalidateForPolicy($policy);

        return back()->with('success', 'Policy status updated.');
    }

    public function destroyPolicy(Policy $policy)
    {
        abort_unless($policy->school_id == $this->schoolId(), 403);

        $policy->update(['is_deleted' => true, 'status' => false]);
        $this->invalidateForPolicy($policy);

        return back()->with('success', 'Policy deleted.');
    }

    // ─── Roles ───────────────────────────────────────────────────────────

    public function storeRole(Request $request)
    {
        $schoolId = $this->schoolId();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'policy_ids' => 'array',
            'policy_ids.*' => 'integer|exists:policies,id',
        ]);

        $role = Role::create([
            'school_id' => $schoolId,
            'name' => $validated['name'],
            'created_by' => auth()->id(),
        ]);

        $role->policies()->sync($validated['policy_ids'] ?? []);

        return back()->with('success', 'Role created.');
    }

    public function updateRole(Request $request, Role $role)
    {
        abort_unless($role->school_id == $this->schoolId(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'policy_ids' => 'array',
            'policy_ids.*' => 'integer|exists:policies,id',
        ]);

        $role->update(['name' => $validated['name']]);
        $role->policies()->sync($validated['policy_ids'] ?? []);

        $this->invalidateForRole($role);

        return back()->with('success', 'Role updated.');
    }

    public function toggleRoleStatus(Role $role)
    {
        abort_unless($role->school_id == $this->schoolId(), 403);

        $role->update(['status' => !$role->status]);
        $this->invalidateForRole($role);

        return back()->with('success', 'Role status updated.');
    }

    public function destroyRole(Role $role)
    {
        abort_unless($role->school_id == $this->schoolId(), 403);

        $role->update(['is_deleted' => true, 'status' => false]);
        $this->invalidateForRole($role);

        return back()->with('success', 'Role deleted.');
    }

    // ─── Team members ────────────────────────────────────────────────────

    public function storeUser(Request $request)
    {
        $schoolId = $this->schoolId();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:15',
            'panel_role' => 'required|in:sub-admin,incharge',
            'assignment_type' => 'required|in:role,direct',
            'access_role_id' => 'nullable|required_if:assignment_type,role|integer|exists:access_roles,id',
            'policy_ids' => 'array',
            'policy_ids.*' => 'integer|exists:policies,id',
        ]);

        $tempPassword = Str::password(12);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($tempPassword),
            'school_id' => $schoolId,
            'is_active' => true,
            'access_role_id' => $validated['assignment_type'] === 'role' ? $validated['access_role_id'] : null,
        ]);

        $user->assignRole($validated['panel_role']);

        if ($validated['assignment_type'] === 'direct') {
            foreach ($validated['policy_ids'] ?? [] as $policyId) {
                UserDirectPermission::create([
                    'user_id' => $user->id,
                    'policy_id' => $policyId,
                    'assigned_by' => auth()->id(),
                ]);
            }
        }

        return back()->with('success', "Team member added. Temporary password: {$tempPassword}");
    }

    public function updateUser(Request $request, User $user)
    {
        abort_unless($user->school_id == $this->schoolId(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:15',
            'assignment_type' => 'required|in:role,direct',
            'access_role_id' => 'nullable|required_if:assignment_type,role|integer|exists:access_roles,id',
            'policy_ids' => 'array',
            'policy_ids.*' => 'integer|exists:policies,id',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'access_role_id' => $validated['assignment_type'] === 'role' ? $validated['access_role_id'] : null,
        ]);

        $user->directPolicies()->detach();
        if ($validated['assignment_type'] === 'direct') {
            foreach ($validated['policy_ids'] ?? [] as $policyId) {
                UserDirectPermission::create([
                    'user_id' => $user->id,
                    'policy_id' => $policyId,
                    'assigned_by' => auth()->id(),
                ]);
            }
        }

        forget_resource_permission_cache($user->id);

        return back()->with('success', 'Team member updated.');
    }

    public function toggleUserStatus(User $user)
    {
        abort_unless($user->school_id == $this->schoolId(), 403);

        $user->update(['is_active' => !$user->is_active]);
        forget_resource_permission_cache($user->id);

        return back()->with('success', 'Team member status updated.');
    }

    public function resetUserPassword(User $user)
    {
        abort_unless($user->school_id == $this->schoolId(), 403);

        $tempPassword = Str::password(12);
        $user->update(['password' => Hash::make($tempPassword)]);

        return back()->with('success', "Password reset. Temporary password: {$tempPassword}");
    }

    public function destroyUser(User $user)
    {
        abort_unless($user->school_id == $this->schoolId(), 403);

        $user->delete();
        forget_resource_permission_cache($user->id);

        return back()->with('success', 'Team member removed.');
    }

    // ─── Cache invalidation helpers ──────────────────────────────────────

    private function invalidateForPolicy(Policy $policy): void
    {
        $directUserIds = UserDirectPermission::where('policy_id', $policy->id)->pluck('user_id');
        $roleIds = \App\Models\Admin\RolePermission::where('policy_id', $policy->id)->pluck('role_id');
        $roleUserIds = User::whereIn('access_role_id', $roleIds)->pluck('id');

        $directUserIds->merge($roleUserIds)->unique()->each(fn ($id) => forget_resource_permission_cache($id));
    }

    private function invalidateForRole(Role $role): void
    {
        User::where('access_role_id', $role->id)->pluck('id')
            ->each(fn ($id) => forget_resource_permission_cache($id));
    }
}
