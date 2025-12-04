<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\CheckpointController as AdminCheckpointController;
use App\Http\Controllers\Admin\PatrolReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\EmployeeProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use App\Http\Controllers\CompanyProfileController;

Route::get('/', [CompanyProfileController::class, 'home'])->name('home');
Route::get('/profile', [CompanyProfileController::class, 'profile'])->name('profile');
Route::get('/services', [CompanyProfileController::class, 'services'])->name('services');
Route::get('/achievements', [CompanyProfileController::class, 'achievements'])->name('achievements');
Route::get('/activities', [CompanyProfileController::class, 'activities'])->name('activities');
Route::get('/activities/{activity:slug}', [CompanyProfileController::class, 'activityDetail'])->name('activities.show');
Route::get('/clients', [CompanyProfileController::class, 'clients'])->name('clients');
Route::get('/career', [CompanyProfileController::class, 'career'])->name('career');
Route::get('/career/{career}/apply', [CompanyProfileController::class, 'showApplyForm'])->name('career.apply-form');
Route::post('/career/apply', [CompanyProfileController::class, 'sendApplication'])->name('career.apply');
Route::get('/contact', [CompanyProfileController::class, 'contact'])->name('contact');
Route::post('/contact', [CompanyProfileController::class, 'sendContact'])->name('contact.send');

// Form PDP untuk karyawan (publik)
Route::get('/pdp', [EmployeeProfileController::class, 'showForm'])->name('pdp.form');
Route::post('/pdp', [EmployeeProfileController::class, 'submitForm'])->name('pdp.submit');

Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::middleware(['auth', 'role:SUPERADMIN,ADMIN,PROJECT_ADMIN'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', AdminUserController::class)->except(['show']);
    Route::get('users-import', [AdminUserController::class, 'showImportForm'])->name('users.import.form');
    Route::post('users-import', [AdminUserController::class, 'import'])->name('users.import.store');
    Route::get('users-import-template', [AdminUserController::class, 'downloadImportTemplate'])->name('users.import.template');

    Route::resource('projects', AdminProjectController::class)->except(['show']);
    Route::get('projects/{project}/shifts', [AdminProjectController::class, 'editShifts'])->name('projects.shifts.edit');
    Route::post('projects/{project}/shifts', [AdminProjectController::class, 'updateShifts'])->name('projects.shifts.update');
    
    Route::resource('shifts', \App\Http\Controllers\Admin\ShiftController::class)->except(['show']);

    Route::get('projects/{project}/pkwt', [AdminProjectController::class, 'editPkwt'])->name('projects.pkwt.edit');
    Route::put('projects/{project}/pkwt', [AdminProjectController::class, 'updatePkwt'])->name('projects.pkwt.update');

    Route::get('reports/attendance', [AttendanceReportController::class, 'index'])->name('reports.attendance');
    Route::get('reports/attendance/export-excel', [AttendanceReportController::class, 'exportExcel'])->name('reports.attendance.exportExcel');
    Route::get('reports/attendance/export-pdf', [AttendanceReportController::class, 'exportPdf'])->name('reports.attendance.exportPdf');

    Route::get('reports/patrol', [PatrolReportController::class, 'index'])->name('reports.patrol');
    Route::get('reports/patrol/export-excel', [PatrolReportController::class, 'exportExcel'])->name('reports.patrol.exportExcel');
    Route::get('reports/patrol/export-pdf', [PatrolReportController::class, 'exportPdf'])->name('reports.patrol.exportPdf');

    Route::get('approvals/attendance', [ApprovalController::class, 'attendance'])->name('approvals.attendance');
    Route::post('approvals/attendance/{attendanceLog}/approve', [ApprovalController::class, 'approveAttendance'])->name('approvals.attendance.approve');
    Route::post('approvals/attendance/{attendanceLog}/reject', [ApprovalController::class, 'rejectAttendance'])->name('approvals.attendance.reject');

    Route::get('approvals/leave', [ApprovalController::class, 'leave'])->name('approvals.leave');
    Route::post('approvals/leave/{leaveRequest}/approve', [ApprovalController::class, 'approveLeave'])->name('approvals.leave.approve');
    Route::post('approvals/leave/{leaveRequest}/reject', [ApprovalController::class, 'rejectLeave'])->name('approvals.leave.reject');

    Route::resource('patrol-checkpoints', AdminCheckpointController::class)
        ->names('patrol.checkpoints')
        ->parameters(['patrol-checkpoints' => 'checkpoint'])
        ->except(['show']);
    Route::get('patrol-checkpoints/{checkpoint}/print', [AdminCheckpointController::class, 'print'])->name('patrol.checkpoints.print');
    Route::get('patrol-checkpoints-print-all', [AdminCheckpointController::class, 'printAll'])->name('patrol.checkpoints.printAll');

    Route::get('payroll', static function () {
        return view('admin.payroll.index');
    })->name('payroll.index');

    Route::get('pkwt', static function () {
        return view('admin.pkwt.index');
    })->name('pkwt.index');

    Route::get('hrd/applications', [\App\Http\Controllers\Admin\JobApplicationController::class, 'index'])->name('hrd.applications');
    Route::get('hrd/rejected', [\App\Http\Controllers\Admin\JobApplicationController::class, 'index'])->name('hrd.rejected');
    Route::get('hrd/applications/{application}', [\App\Http\Controllers\Admin\JobApplicationController::class, 'show'])->name('hrd.applications.show');
    Route::put('hrd/applications/{application}/status', [\App\Http\Controllers\Admin\JobApplicationController::class, 'updateStatus'])->name('hrd.applications.status');
    Route::delete('hrd/applications/{application}', [\App\Http\Controllers\Admin\JobApplicationController::class, 'destroy'])->name('hrd.applications.destroy');

    // CMS Routes
    Route::resource('cms-contents', \App\Http\Controllers\Admin\CmsContentController::class)
        ->parameters(['cms-contents' => 'content']) // Explicitly map route param 'cms-contents' to model binding variable 'content'
        ->only(['index', 'edit', 'update']);
    Route::resource('cms-hero-slides', \App\Http\Controllers\Admin\CmsHeroSlideController::class)
        ->parameters(['cms-hero-slides' => 'heroSlide'])
        ->except(['show']);
    Route::resource('cms-services', \App\Http\Controllers\Admin\CmsServiceController::class)
        ->parameters(['cms-services' => 'service'])
        ->except(['show']);
    Route::resource('cms-achievements', \App\Http\Controllers\Admin\CmsAchievementController::class)
        ->parameters(['cms-achievements' => 'achievement'])
        ->except(['show']);
    Route::resource('cms-activities', \App\Http\Controllers\Admin\CmsActivityController::class)
        ->parameters(['cms-activities' => 'activity'])
        ->except(['show']);
    Route::resource('cms-clients', \App\Http\Controllers\Admin\CmsClientController::class)
        ->parameters(['cms-clients' => 'client'])
        ->except(['show']);
    Route::resource('cms-careers', \App\Http\Controllers\Admin\CmsCareerController::class)
        ->parameters(['cms-careers' => 'career'])
        ->except(['show']);
    Route::resource('cms-contacts', \App\Http\Controllers\Admin\ContactMessageController::class)
        ->parameters(['cms-contacts' => 'contactMessage'])
        ->only(['index', 'show', 'destroy']);
});
