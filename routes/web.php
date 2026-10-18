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
use App\Http\Controllers\SuperAdmin\EnquiryController;
use App\Http\Controllers\Admin\ManageTeamsController;
use App\Http\Controllers\Admin\AccessControlController;
use App\Http\Controllers\Admin\TransportController;
use App\Http\Controllers\Admin\LibraryController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\HostelController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\AssignmentController;

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

    // OTP login (email / mobile) — AJAX
    Route::post('/login/otp/request', [LoginController::class, 'requestOtp'])->name('login.otp.request');
    Route::post('/login/otp/verify', [LoginController::class, 'verifyOtp'])->name('login.otp.verify');
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
        Route::get('/revenue/export', [SubscriptionController::class, 'exportRevenue'])->name('revenue.export');

        // Enquiries (leads from the public marketing website)
        Route::prefix('enquiries')->name('enquiries.')->group(function () {
            Route::get('/', [EnquiryController::class, 'index'])->name('index');
            Route::get('/{enquiry}', [EnquiryController::class, 'show'])->name('show');
            Route::put('/{enquiry}', [EnquiryController::class, 'update'])->name('update');
            Route::delete('/{enquiry}', [EnquiryController::class, 'destroy'])->name('destroy');
        });

        // All module management (super admin can do everything)
        Route::resource('students', StudentController::class);
        Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
        Route::get('/students/{id}/books', [StudentController::class, 'books'])->name('students.books');
        Route::get('/students/{id}/transport', [StudentController::class, 'transport'])->name('students.transport');
        Route::get('/students/{id}/attendance', [StudentController::class, 'attendance'])->name('students.attendance');
        Route::resource('teachers', TeacherController::class);

        // Attendance
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('/students', [AttendanceController::class, 'studentAttendance'])->name('students');
            Route::get('/students/roster', [AttendanceController::class, 'studentRoster'])->name('students.roster');
            Route::post('/students/mark', [AttendanceController::class, 'markStudentAttendance'])->name('students.mark');
            Route::get('/staff', [AttendanceController::class, 'staffAttendance'])->name('staff');
            Route::get('/staff/roster', [AttendanceController::class, 'staffRoster'])->name('staff.roster');
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
            Route::get('/invoices/{id}/document', [FeeController::class, 'invoiceDocument'])->name('invoices.document');

            // Category-based fees
            Route::get('/categories', [FeeController::class, 'categories'])->name('categories');
            Route::post('/categories', [FeeController::class, 'storeCategory'])->name('categories.store');
            Route::delete('/categories/{id}', [FeeController::class, 'deleteCategory'])->name('categories.delete');
            Route::post('/plans', [FeeController::class, 'storePlan'])->name('plans.store');
            Route::delete('/plans/{id}', [FeeController::class, 'deletePlan'])->name('plans.delete');
            Route::post('/categories/generate', [FeeController::class, 'generateCategory'])->name('categories.generate');

            // Class-wise pending + manual payment
            Route::get('/pending', [FeeController::class, 'pending'])->name('pending');
            Route::post('/invoices/{id}/pay-offline', [FeeController::class, 'payOffline'])->name('invoices.pay-offline');

            // Per-student fee override
            Route::post('/student-override', [FeeController::class, 'setStudentOverride'])->name('student-override');
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
            Route::put('/vehicles/{id}', [TransportController::class, 'updateVehicle'])->name('vehicles.update');
            Route::post('/assign', [TransportController::class, 'assignStudent'])->name('assign');
            Route::delete('/assign/{id}', [TransportController::class, 'unassignStudent'])->name('unassign');
        });

        // Library
        Route::prefix('library')->name('library.')->group(function () {
            Route::get('/', [LibraryController::class, 'index'])->name('index');
            Route::post('/books', [LibraryController::class, 'store'])->name('books.store');
            Route::put('/books/{id}', [LibraryController::class, 'update'])->name('books.update');
            Route::delete('/books/{id}', [LibraryController::class, 'destroy'])->name('books.destroy');
            Route::post('/assign', [LibraryController::class, 'assign'])->name('assign');
            Route::get('/issues', [LibraryController::class, 'issues'])->name('issues');
            Route::post('/issues/{id}/return', [LibraryController::class, 'returnBook'])->name('issues.return');
            Route::post('/issues/{id}/fine-paid', [LibraryController::class, 'markFinePaid'])->name('issues.fine-paid');
        });

        // Events
        Route::prefix('events')->name('events.')->group(function () {
            Route::get('/', [EventController::class, 'index'])->name('index');
            Route::post('/', [EventController::class, 'store'])->name('store');
            Route::put('/{id}', [EventController::class, 'update'])->name('update');
            Route::delete('/{id}', [EventController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/publish', [EventController::class, 'publish'])->name('publish');
            Route::post('/{id}/cancel', [EventController::class, 'cancel'])->name('cancel');
            Route::get('/{id}/registrations', [EventController::class, 'registrations'])->name('registrations');
        });

        // Banners (slider + popup)
        Route::prefix('banners')->name('banners.')->group(function () {
            Route::get('/', [BannerController::class, 'index'])->name('index');
            Route::post('/', [BannerController::class, 'store'])->name('store');
            Route::post('/{id}', [BannerController::class, 'update'])->name('update');
            Route::delete('/{id}', [BannerController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/toggle', [BannerController::class, 'toggle'])->name('toggle');
            Route::post('/reorder/save', [BannerController::class, 'reorder'])->name('reorder');
        });

        // Notifications (push + in-app)
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('/send', [NotificationController::class, 'send'])->name('send');
        });

        // Store (sellable items + orders)
        Route::prefix('store')->name('store.')->group(function () {
            Route::get('/', [StoreController::class, 'index'])->name('index');
            Route::post('/', [StoreController::class, 'store'])->name('store');
            Route::post('/{id}', [StoreController::class, 'update'])->name('update');
            Route::delete('/{id}', [StoreController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/toggle', [StoreController::class, 'toggle'])->name('toggle');
            Route::get('/orders/list', [StoreController::class, 'orders'])->name('orders');
            Route::post('/orders/{id}/complete', [StoreController::class, 'complete'])->name('orders.complete');
        });

        // Hostel
        Route::prefix('hostel')->name('hostel.')->group(function () {
            Route::get('/', [HostelController::class, 'index'])->name('index');
            Route::post('/blocks', [HostelController::class, 'storeBlock'])->name('blocks.store');
            Route::post('/rooms', [HostelController::class, 'storeRoom'])->name('rooms.store');
            Route::post('/allocate', [HostelController::class, 'allocate'])->name('allocate');
            Route::delete('/allocations/{id}', [HostelController::class, 'vacate'])->name('vacate');
        });

        // Inventory
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::post('/categories', [InventoryController::class, 'storeCategory'])->name('categories.store');
            Route::post('/items', [InventoryController::class, 'storeItem'])->name('items.store');
            Route::put('/items/{id}', [InventoryController::class, 'updateItem'])->name('items.update');
            Route::post('/stock', [InventoryController::class, 'stock'])->name('stock');
            Route::get('/transactions', [InventoryController::class, 'transactions'])->name('transactions');
        });

        // Payroll
        Route::prefix('payroll')->name('payroll.')->group(function () {
            Route::get('/', [PayrollController::class, 'index'])->name('index');
            Route::post('/structure', [PayrollController::class, 'storeStructure'])->name('structure.store');
            Route::post('/payslip', [PayrollController::class, 'generatePayslip'])->name('payslip.generate');
            Route::post('/payslip/{id}/paid', [PayrollController::class, 'markPaid'])->name('payslip.paid');
            Route::get('/payslip/{id}/document', [PayrollController::class, 'payslipDocument'])->name('payslip.document');
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

        // Assignments (create, grade, view submissions)
        Route::prefix('assignments')->name('assignments.')->group(function () {
            Route::get('/', [AssignmentController::class, 'index'])->name('index');
            Route::post('/', [AssignmentController::class, 'store'])->name('store');
            Route::delete('/{id}', [AssignmentController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/submissions', [AssignmentController::class, 'submissions'])->name('submissions');
            Route::post('/{id}/grade', [AssignmentController::class, 'grade'])->name('grade');
        });

        // Payment gateway settings (per school — super-admin configures)
        Route::get('/payment-settings', [\App\Http\Controllers\Admin\PaymentSettingsController::class, 'index'])->name('payment-settings.index');
        Route::post('/payment-settings/{schoolId}', [\App\Http\Controllers\Admin\PaymentSettingsController::class, 'update'])->name('payment-settings.update');

        // Subscription invoices (platform bills schools)
        Route::prefix('subscription-invoices')->name('subscription-invoices.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SuperAdmin\SubscriptionInvoiceController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\SuperAdmin\SubscriptionInvoiceController::class, 'store'])->name('store');
            Route::post('/{id}/cancel', [\App\Http\Controllers\SuperAdmin\SubscriptionInvoiceController::class, 'cancel'])->name('cancel');
        });

        // Profile & Settings
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
        Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
        Route::post('/settings', [ProfileController::class, 'updateSettings'])->name('settings.update');
        Route::post('/settings/otp', [ProfileController::class, 'updateOtpSettings'])->name('settings.otp');
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
        Route::get('/students/{id}/books', [StudentController::class, 'books'])->name('students.books');
        Route::get('/students/{id}/transport', [StudentController::class, 'transport'])->name('students.transport');
        Route::get('/students/{id}/attendance', [StudentController::class, 'attendance'])->name('students.attendance');

        // Teachers — full CRUD
        Route::resource('teachers', TeacherController::class);

        // Attendance
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('/students', [AttendanceController::class, 'studentAttendance'])->name('students');
            Route::get('/students/roster', [AttendanceController::class, 'studentRoster'])->name('students.roster');
            Route::post('/students/mark', [AttendanceController::class, 'markStudentAttendance'])->name('students.mark');
            Route::get('/staff', [AttendanceController::class, 'staffAttendance'])->name('staff');
            Route::get('/staff/roster', [AttendanceController::class, 'staffRoster'])->name('staff.roster');
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
            Route::get('/invoices/{id}/document', [FeeController::class, 'invoiceDocument'])->name('invoices.document');

            // Category-based fees
            Route::get('/categories', [FeeController::class, 'categories'])->name('categories');
            Route::post('/categories', [FeeController::class, 'storeCategory'])->name('categories.store');
            Route::delete('/categories/{id}', [FeeController::class, 'deleteCategory'])->name('categories.delete');
            Route::post('/plans', [FeeController::class, 'storePlan'])->name('plans.store');
            Route::delete('/plans/{id}', [FeeController::class, 'deletePlan'])->name('plans.delete');
            Route::post('/categories/generate', [FeeController::class, 'generateCategory'])->name('categories.generate');

            // Class-wise pending + manual payment
            Route::get('/pending', [FeeController::class, 'pending'])->name('pending');
            Route::post('/invoices/{id}/pay-offline', [FeeController::class, 'payOffline'])->name('invoices.pay-offline');

            // Per-student fee override
            Route::post('/student-override', [FeeController::class, 'setStudentOverride'])->name('student-override');
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
            Route::put('/vehicles/{id}', [TransportController::class, 'updateVehicle'])->name('vehicles.update');
            Route::post('/assign', [TransportController::class, 'assignStudent'])->name('assign');
            Route::delete('/assign/{id}', [TransportController::class, 'unassignStudent'])->name('unassign');
        });

        // Library
        Route::prefix('library')->name('library.')->group(function () {
            Route::get('/', [LibraryController::class, 'index'])->name('index');
            Route::post('/books', [LibraryController::class, 'store'])->name('books.store');
            Route::put('/books/{id}', [LibraryController::class, 'update'])->name('books.update');
            Route::delete('/books/{id}', [LibraryController::class, 'destroy'])->name('books.destroy');
            Route::post('/assign', [LibraryController::class, 'assign'])->name('assign');
            Route::get('/issues', [LibraryController::class, 'issues'])->name('issues');
            Route::post('/issues/{id}/return', [LibraryController::class, 'returnBook'])->name('issues.return');
            Route::post('/issues/{id}/fine-paid', [LibraryController::class, 'markFinePaid'])->name('issues.fine-paid');
        });

        // Events
        Route::prefix('events')->name('events.')->group(function () {
            Route::get('/', [EventController::class, 'index'])->name('index');
            Route::post('/', [EventController::class, 'store'])->name('store');
            Route::put('/{id}', [EventController::class, 'update'])->name('update');
            Route::delete('/{id}', [EventController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/publish', [EventController::class, 'publish'])->name('publish');
            Route::post('/{id}/cancel', [EventController::class, 'cancel'])->name('cancel');
            Route::get('/{id}/registrations', [EventController::class, 'registrations'])->name('registrations');
        });

        // Banners (slider + popup)
        Route::prefix('banners')->name('banners.')->group(function () {
            Route::get('/', [BannerController::class, 'index'])->name('index');
            Route::post('/', [BannerController::class, 'store'])->name('store');
            Route::post('/{id}', [BannerController::class, 'update'])->name('update');
            Route::delete('/{id}', [BannerController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/toggle', [BannerController::class, 'toggle'])->name('toggle');
            Route::post('/reorder/save', [BannerController::class, 'reorder'])->name('reorder');
        });

        // Notifications (push + in-app)
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('/send', [NotificationController::class, 'send'])->name('send');
        });

        // Store (sellable items + orders)
        Route::prefix('store')->name('store.')->group(function () {
            Route::get('/', [StoreController::class, 'index'])->name('index');
            Route::post('/', [StoreController::class, 'store'])->name('store');
            Route::post('/{id}', [StoreController::class, 'update'])->name('update');
            Route::delete('/{id}', [StoreController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/toggle', [StoreController::class, 'toggle'])->name('toggle');
            Route::get('/orders/list', [StoreController::class, 'orders'])->name('orders');
            Route::post('/orders/{id}/complete', [StoreController::class, 'complete'])->name('orders.complete');
        });

        // Hostel
        Route::prefix('hostel')->name('hostel.')->group(function () {
            Route::get('/', [HostelController::class, 'index'])->name('index');
            Route::post('/blocks', [HostelController::class, 'storeBlock'])->name('blocks.store');
            Route::post('/rooms', [HostelController::class, 'storeRoom'])->name('rooms.store');
            Route::post('/allocate', [HostelController::class, 'allocate'])->name('allocate');
            Route::delete('/allocations/{id}', [HostelController::class, 'vacate'])->name('vacate');
        });

        // Inventory
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::post('/categories', [InventoryController::class, 'storeCategory'])->name('categories.store');
            Route::post('/items', [InventoryController::class, 'storeItem'])->name('items.store');
            Route::put('/items/{id}', [InventoryController::class, 'updateItem'])->name('items.update');
            Route::post('/stock', [InventoryController::class, 'stock'])->name('stock');
            Route::get('/transactions', [InventoryController::class, 'transactions'])->name('transactions');
        });

        // Payroll
        Route::prefix('payroll')->name('payroll.')->group(function () {
            Route::get('/', [PayrollController::class, 'index'])->name('index');
            Route::post('/structure', [PayrollController::class, 'storeStructure'])->name('structure.store');
            Route::post('/payslip', [PayrollController::class, 'generatePayslip'])->name('payslip.generate');
            Route::post('/payslip/{id}/paid', [PayrollController::class, 'markPaid'])->name('payslip.paid');
            Route::get('/payslip/{id}/document', [PayrollController::class, 'payslipDocument'])->name('payslip.document');
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

        // Assignments (create, grade, view submissions)
        Route::prefix('assignments')->name('assignments.')->group(function () {
            Route::get('/', [AssignmentController::class, 'index'])->name('index');
            Route::post('/', [AssignmentController::class, 'store'])->name('store');
            Route::delete('/{id}', [AssignmentController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/submissions', [AssignmentController::class, 'submissions'])->name('submissions');
            Route::post('/{id}/grade', [AssignmentController::class, 'grade'])->name('grade');
        });

        // Pay subscription (school-admin pays the platform)
        Route::get('/subscription', [\App\Http\Controllers\SuperAdmin\SubscriptionInvoiceController::class, 'mySubscription'])->name('subscription');
        Route::post('/subscription/{id}/pay', [\App\Http\Controllers\SuperAdmin\SubscriptionInvoiceController::class, 'initiatePay'])->name('subscription.pay');
        Route::post('/subscription/{id}/verify', [\App\Http\Controllers\SuperAdmin\SubscriptionInvoiceController::class, 'verifyPay'])->name('subscription.verify');

        // Profile & Settings
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
        Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
        Route::post('/settings', [ProfileController::class, 'updateSettings'])->name('settings.update');
        Route::post('/settings/otp', [ProfileController::class, 'updateOtpSettings'])->name('settings.otp');
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
