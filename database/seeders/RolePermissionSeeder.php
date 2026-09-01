<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─────────────────────────────────────────────────────────────────
        // Create Permissions
        // ─────────────────────────────────────────────────────────────────
        $permissions = [
            // Dashboard
            'view-dashboard',

            // Student Management
            'manage-students',
            'view-students',
            'create-students',
            'edit-students',
            'delete-students',
            'import-students',
            'export-students',

            // Teacher Management
            'manage-teachers',
            'view-teachers',
            'create-teachers',
            'edit-teachers',
            'delete-teachers',

            // Staff Management
            'manage-staff',
            'view-staff',

            // Attendance
            'manage-attendance',
            'mark-student-attendance',
            'mark-staff-attendance',
            'view-attendance-report',

            // Fee Management
            'manage-fees',
            'view-fees',
            'create-fee-structure',
            'generate-invoices',
            'record-payment',
            'view-defaulters',

            // Exam Management
            'manage-exams',
            'create-exams',
            'enter-marks',
            'view-results',
            'generate-report-cards',

            // Timetable
            'manage-timetable',

            // Assignments
            'manage-assignments',

            // Library
            'manage-library',

            // Transport
            'manage-transport',

            // Notifications
            'send-notifications',

            // Reports
            'view-reports',
            'export-reports',

            // Settings
            'manage-settings',
            'manage-users',
            'manage-roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ─────────────────────────────────────────────────────────────────
        // Create Roles
        // ─────────────────────────────────────────────────────────────────

        // Super Admin - Platform level (all permissions)
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        // School Admin - Full access within their school
        $schoolAdmin = Role::firstOrCreate(['name' => 'school-admin', 'guard_name' => 'web']);
        $schoolAdmin->givePermissionTo(Permission::all());

        // Sub Admin - Configurable permissions per school
        $subAdmin = Role::firstOrCreate(['name' => 'sub-admin', 'guard_name' => 'web']);
        $subAdmin->givePermissionTo(['view-dashboard']); // Minimal default, configured per user

        // Class Incharge - Limited to their assigned class
        $incharge = Role::firstOrCreate(['name' => 'incharge', 'guard_name' => 'web']);
        $incharge->givePermissionTo([
            'view-dashboard',
            'view-students',
            'mark-student-attendance',
            'view-attendance-report',
            'view-results',
        ]);

        // Teacher role (for basic teacher login if needed)
        $teacher = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $teacher->givePermissionTo([
            'view-dashboard',
            'view-students',
            'mark-student-attendance',
            'enter-marks',
        ]);
    }
}
