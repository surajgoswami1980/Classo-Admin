<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\FeeController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\TimetableController;
use App\Http\Controllers\Admin\AcademicController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\SuperAdmin\SchoolController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SubscriptionController;
use App\Http\Controllers\SuperAdmin\SchoolSwitchController;
use App\Http\Controllers\Admin\ManageTeamsController;
use App\Http\Controllers\Admin\AccessControlController;
use App\Http\Controllers\Admin\TransportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| SUPER ADMIN → /admin/* (NO permission checks, full access)
| SCHOOL CLIENT → /user/* (Permission-based, like Educrypt)
|--------------------------------------------------------------------------
*/

// ─── Guest ───────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// ─── SUPER ADMIN ROUTES (/admin/*) ──────────────────────────────────────
// NO role/permission middleware — super admin bypasses everything
Route::middleware(['auth', 'super.admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

        // School Management
        Route::resource('schools', SchoolController::class);
        Route::post('/schools/{school}/activate', [SchoolController::class, 'activate'])->name('schools.activate');
        Route::post('/schools/{school}/deactivate', [SchoolController::class, 'deactivate'])->name('schools.deactivate');
        Route::post('/schools/{school}/switch', [SchoolSwitchController::class, 'switch'])->name('schools.switch');

        // Subscriptions & Revenue
        Route::resource('subscriptions', SubscriptionController::class);
        Route::get('/revenue', [SubscriptionController::class, 'revenue'])->name('revenue');

        // All module management (super admin can do everything)
        Route::resource('students', StudentController::class);
        Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
        Route::resource('teachers', TeacherController::class);

        // Attendance
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('/students', [AttendanceController::class, 'studentAttendance'])->name('students');
            Route::post('/students/mark', [AttendanceController::class, 'markStudentAttendance'])->name('students.mark');
            Route::get('/staff', [AttendanceController::class, 'staffAttendance'])->name('staff');
            Route::post('/staff/mark', [AttendanceController::class, 'markStaffAttendance'])->name('staff.mark');
            Route::get('/report', [AttendanceController::class, 'report'])->name('report');
        });

        // Fees
        Route::prefix('fees')->name('fees.')->group(function () {
            Route::get('/', [FeeController::class, 'index'])->name('index');
            Route::get('/structure', [FeeController::class, 'structure'])->name('structure');
            Route::post('/structure', [FeeController::class, 'storeStructure'])->name('structure.store');
            Route::get('/invoices', [FeeController::class, 'invoices'])->name('invoices');
            Route::post('/invoices/generate', [FeeController::class, 'generateInvoices'])->name('invoices.generate');
            Route::get('/defaulters', [FeeController::class, 'defaulters'])->name('defaulters');
            Route::get('/collection-report', [FeeController::class, 'collectionReport'])->name('collection-report');
        });

        // Exams
        Route::prefix('exams')->name('exams.')->group(function () {
            Route::get('/', [ExamController::class, 'index'])->name('index');
            Route::post('/create', [ExamController::class, 'store'])->name('store');
            Route::get('/{exam}/marks', [ExamController::class, 'marks'])->name('marks');
            Route::post('/{exam}/marks', [ExamController::class, 'storeMarks'])->name('marks.store');
            Route::get('/{exam}/results', [ExamController::class, 'results'])->name('results');
            Route::get('/report-cards/{student}', [ExamController::class, 'reportCard'])->name('report-card');
        });

        // Timetable
        Route::prefix('timetable')->name('timetable.')->group(function () {
            Route::get('/', [TimetableController::class, 'index'])->name('index');
            Route::post('/create', [TimetableController::class, 'store'])->name('store');
            Route::delete('/{id}', [TimetableController::class, 'destroy'])->name('destroy');
        });

        // Transport
        Route::prefix('transport')->name('transport.')->group(function () {
            Route::get('/', [TransportController::class, 'index'])->name('index');
            Route::post('/routes', [TransportController::class, 'storeRoute'])->name('routes.store');
            Route::post('/stops', [TransportController::class, 'storeStop'])->name('stops.store');
            Route::delete('/stops/{id}', [TransportController::class, 'destroyStop'])->name('stops.destroy');
            Route::post('/vehicles', [TransportController::class, 'storeVehicle'])->name('vehicles.store');
            Route::post('/assign', [TransportController::class, 'assignStudent'])->name('assign');
            Route::delete('/assign/{id}', [TransportController::class, 'unassignStudent'])->name('unassign');
        });

        // Academic Structure (Sessions, Classes, Sections)
        Route::prefix('academic')->name('academic.')->group(function () {
            Route::get('/sessions', [AcademicController::class, 'sessions'])->name('sessions');
            Route::post('/sessions', [AcademicController::class, 'storeSession'])->name('sessions.store');
            Route::get('/classes', [AcademicController::class, 'classes'])->name('classes');
            Route::post('/classes', [AcademicController::class, 'storeClass'])->name('classes.store');
            Route::delete('/classes/{id}', [AcademicController::class, 'destroyClass'])->name('classes.destroy');
            Route::get('/sections', [AcademicController::class, 'sections'])->name('sections');
            Route::post('/sections', [AcademicController::class, 'storeSection'])->name('sections.store');
            Route::delete('/sections/{id}', [AcademicController::class, 'destroySection'])->name('sections.destroy');
        });

        // Team Management (roles, policies, team members)
        Route::prefix('team')->name('team.')->group(function () {
            Route::get('/', [ManageTeamsController::class, 'index'])->name('index');
            Route::post('/policies', [ManageTeamsController::class, 'storePolicy'])->name('policies.store');
            Route::put('/policies/{policy}', [ManageTeamsController::class, 'updatePolicy'])->name('policies.update');
            Route::delete('/policies/{policy}', [ManageTeamsController::class, 'destroyPolicy'])->name('policies.destroy');
            Route::patch('/policies/{policy}/status', [ManageTeamsController::class, 'togglePolicyStatus'])->name('policies.status');
            Route::post('/roles', [ManageTeamsController::class, 'storeRole'])->name('roles.store');
            Route::put('/roles/{role}', [ManageTeamsController::class, 'updateRole'])->name('roles.update');
            Route::delete('/roles/{role}', [ManageTeamsController::class, 'destroyRole'])->name('roles.destroy');
            Route::patch('/roles/{role}/status', [ManageTeamsController::class, 'toggleRoleStatus'])->name('roles.status');
            Route::post('/users', [ManageTeamsController::class, 'storeUser'])->name('users.store');
            Route::put('/users/{user}', [ManageTeamsController::class, 'updateUser'])->name('users.update');
            Route::patch('/users/{user}/status', [ManageTeamsController::class, 'toggleUserStatus'])->name('users.status');
            Route::post('/users/{user}/reset-password', [ManageTeamsController::class, 'resetUserPassword'])->name('users.password.reset');
            Route::delete('/users/{user}', [ManageTeamsController::class, 'destroyUser'])->name('users.destroy');
        });

        // Resource/Permission catalog management (platform-wide, super-admin only)
        Route::prefix('resources')->name('resources.')->group(function () {
            Route::get('/', [AccessControlController::class, 'index'])->name('index');
            Route::post('/', [AccessControlController::class, 'storeResource'])->name('store');
            Route::put('/{resource}', [AccessControlController::class, 'updateResource'])->name('update');
            Route::patch('/{resource}/status', [AccessControlController::class, 'toggleResourceStatus'])->name('status');
            Route::post('/{resource}/actions', [AccessControlController::class, 'storeAction'])->name('actions.store');
            Route::put('/actions/{action}', [AccessControlController::class, 'updateAction'])->name('actions.update');
            Route::patch('/actions/{action}/status', [AccessControlController::class, 'toggleActionStatus'])->name('actions.status');
        });

        // Profile & Settings
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
        Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
        Route::post('/settings', [ProfileController::class, 'updateSettings'])->name('settings.update');
    });

// ─── SCHOOL CLIENT ROUTES (/user/*) ─────────────────────────────────────
// Full access for school-admin; permission-gated for sub-admin/incharge — scoped by school_id
Route::middleware(['auth', 'school.tenant', 'resource.permission'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Students — full CRUD
        Route::resource('students', StudentController::class);
        Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');

        // Teachers — full CRUD
        Route::resource('teachers', TeacherController::class);

        // Attendance
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('/students', [AttendanceController::class, 'studentAttendance'])->name('students');
            Route::post('/students/mark', [AttendanceController::class, 'markStudentAttendance'])->name('students.mark');
            Route::get('/staff', [AttendanceController::class, 'staffAttendance'])->name('staff');
            Route::post('/staff/mark', [AttendanceController::class, 'markStaffAttendance'])->name('staff.mark');
            Route::get('/report', [AttendanceController::class, 'report'])->name('report');
        });

        // Fees
        Route::prefix('fees')->name('fees.')->group(function () {
            Route::get('/', [FeeController::class, 'index'])->name('index');
            Route::get('/structure', [FeeController::class, 'structure'])->name('structure');
            Route::post('/structure', [FeeController::class, 'storeStructure'])->name('structure.store');
            Route::get('/invoices', [FeeController::class, 'invoices'])->name('invoices');
            Route::post('/invoices/generate', [FeeController::class, 'generateInvoices'])->name('invoices.generate');
            Route::get('/defaulters', [FeeController::class, 'defaulters'])->name('defaulters');
            Route::get('/collection-report', [FeeController::class, 'collectionReport'])->name('collection-report');
        });

        // Exams
        Route::prefix('exams')->name('exams.')->group(function () {
            Route::get('/', [ExamController::class, 'index'])->name('index');
            Route::post('/create', [ExamController::class, 'store'])->name('store');
            Route::get('/{exam}/marks', [ExamController::class, 'marks'])->name('marks');
            Route::post('/{exam}/marks', [ExamController::class, 'storeMarks'])->name('marks.store');
            Route::get('/{exam}/results', [ExamController::class, 'results'])->name('results');
            Route::get('/report-cards/{student}', [ExamController::class, 'reportCard'])->name('report-card');
        });

        // Timetable
        Route::prefix('timetable')->name('timetable.')->group(function () {
            Route::get('/', [TimetableController::class, 'index'])->name('index');
            Route::post('/create', [TimetableController::class, 'store'])->name('store');
            Route::delete('/{id}', [TimetableController::class, 'destroy'])->name('destroy');
        });

        // Transport
        Route::prefix('transport')->name('transport.')->group(function () {
            Route::get('/', [TransportController::class, 'index'])->name('index');
            Route::post('/routes', [TransportController::class, 'storeRoute'])->name('routes.store');
            Route::post('/stops', [TransportController::class, 'storeStop'])->name('stops.store');
            Route::delete('/stops/{id}', [TransportController::class, 'destroyStop'])->name('stops.destroy');
            Route::post('/vehicles', [TransportController::class, 'storeVehicle'])->name('vehicles.store');
            Route::post('/assign', [TransportController::class, 'assignStudent'])->name('assign');
            Route::delete('/assign/{id}', [TransportController::class, 'unassignStudent'])->name('unassign');
        });

        // Academic Structure
        Route::prefix('academic')->name('academic.')->group(function () {
            Route::get('/sessions', [AcademicController::class, 'sessions'])->name('sessions');
            Route::post('/sessions', [AcademicController::class, 'storeSession'])->name('sessions.store');
            Route::get('/classes', [AcademicController::class, 'classes'])->name('classes');
            Route::post('/classes', [AcademicController::class, 'storeClass'])->name('classes.store');
            Route::delete('/classes/{id}', [AcademicController::class, 'destroyClass'])->name('classes.destroy');
            Route::get('/sections', [AcademicController::class, 'sections'])->name('sections');
            Route::post('/sections', [AcademicController::class, 'storeSection'])->name('sections.store');
            Route::delete('/sections/{id}', [AcademicController::class, 'destroySection'])->name('sections.destroy');
        });

        // Team Management (roles, policies, team members)
        Route::prefix('team')->name('team.')->group(function () {
            Route::get('/', [ManageTeamsController::class, 'index'])->name('index');
            Route::post('/policies', [ManageTeamsController::class, 'storePolicy'])->name('policies.store');
            Route::put('/policies/{policy}', [ManageTeamsController::class, 'updatePolicy'])->name('policies.update');
            Route::delete('/policies/{policy}', [ManageTeamsController::class, 'destroyPolicy'])->name('policies.destroy');
            Route::patch('/policies/{policy}/status', [ManageTeamsController::class, 'togglePolicyStatus'])->name('policies.status');
            Route::post('/roles', [ManageTeamsController::class, 'storeRole'])->name('roles.store');
            Route::put('/roles/{role}', [ManageTeamsController::class, 'updateRole'])->name('roles.update');
            Route::delete('/roles/{role}', [ManageTeamsController::class, 'destroyRole'])->name('roles.destroy');
            Route::patch('/roles/{role}/status', [ManageTeamsController::class, 'toggleRoleStatus'])->name('roles.status');
            Route::post('/users', [ManageTeamsController::class, 'storeUser'])->name('users.store');
            Route::put('/users/{user}', [ManageTeamsController::class, 'updateUser'])->name('users.update');
            Route::patch('/users/{user}/status', [ManageTeamsController::class, 'toggleUserStatus'])->name('users.status');
            Route::post('/users/{user}/reset-password', [ManageTeamsController::class, 'resetUserPassword'])->name('users.password.reset');
            Route::delete('/users/{user}', [ManageTeamsController::class, 'destroyUser'])->name('users.destroy');
        });

        // Profile & Settings
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
        Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
        Route::post('/settings', [ProfileController::class, 'updateSettings'])->name('settings.update');
    });

// ─── Impersonation exit (reachable from inside /user/* while a super-admin
// is viewing a school) — top-level, auth only, not behind school.tenant. ───
Route::post('/impersonation/exit', [SchoolSwitchController::class, 'exit'])
    ->middleware('auth')
    ->name('impersonation.exit');

// ─── Default Redirect ────────────────────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->hasRole('super-admin')) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('user.dashboard');
    }
    return redirect()->route('login');
});
