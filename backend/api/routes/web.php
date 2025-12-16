<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\CheckpointController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\PatrolReportController;
use App\Http\Controllers\Admin\ApprovalController;

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
Route::get('/privacy-policy', [CompanyProfileController::class, 'privacy'])->name('privacy');
Route::get('/faq', [CompanyProfileController::class, 'faq'])->name('faq');
Route::get('/testimonials', [CompanyProfileController::class, 'testimonials'])->name('testimonials');
Route::get('/application', [CompanyProfileController::class, 'application'])->name('application');
Route::get('/application/{application}/download', [CompanyProfileController::class, 'downloadApplication'])->name('application.download');

// Form testimoni publik (via link)
Route::get('/testimonial/{token}', [\App\Http\Controllers\TestimonialFormController::class, 'showForm'])->name('testimonial.form');
Route::post('/testimonial/{token}', [\App\Http\Controllers\TestimonialFormController::class, 'submitForm'])->name('testimonial.submit');

// Form PDP untuk karyawan (publik)
Route::get('/pdp', [EmployeeProfileController::class, 'showForm'])->name('pdp.form');
Route::post('/pdp', [EmployeeProfileController::class, 'submitForm'])->name('pdp.submit');

Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');

Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Admin routes - semua role admin bisa akses
Route::middleware(['auth', 'role:SUPERADMIN,ADMIN,PROJECT_ADMIN,HRD,PAYROLL,CMS'])->prefix('admin')->name('admin.')->group(function (): void {
    
    // Manajemen User Admin - hanya SUPERADMIN
    Route::middleware(['role:SUPERADMIN'])->group(function (): void {
        Route::resource('admin-users', \App\Http\Controllers\Admin\AdminUserController::class)
            ->parameters(['admin-users' => 'admin_user'])
            ->except(['show']);
    });

    // Dashboard - ADMIN, PROJECT_ADMIN
    Route::middleware(['role:SUPERADMIN,ADMIN,PROJECT_ADMIN'])->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show']);
        Route::get('users-import', [\App\Http\Controllers\Admin\UserController::class, 'showImportForm'])->name('users.import.form');
        Route::post('users-import', [\App\Http\Controllers\Admin\UserController::class, 'import'])->name('users.import.store');
        Route::get('users-import-template', [\App\Http\Controllers\Admin\UserController::class, 'downloadImportTemplate'])->name('users.import.template');

        Route::resource('projects', ProjectController::class)->except(['show']);
        Route::get('projects/{project}/shifts', [ProjectController::class, 'editShifts'])->name('projects.shifts.edit');
        Route::post('projects/{project}/shifts', [ProjectController::class, 'updateShifts'])->name('projects.shifts.update');
        
        Route::resource('shifts', \App\Http\Controllers\Admin\ShiftController::class)->except(['show']);

        Route::get('projects/{project}/pkwt', [ProjectController::class, 'editPkwt'])->name('projects.pkwt.edit');
        Route::put('projects/{project}/pkwt', [ProjectController::class, 'updatePkwt'])->name('projects.pkwt.update');

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

        Route::resource('patrol-checkpoints', CheckpointController::class)
            ->names('patrol.checkpoints')
            ->parameters(['patrol-checkpoints' => 'checkpoint'])
            ->except(['show']);
        Route::get('patrol-checkpoints/{checkpoint}/print', [CheckpointController::class, 'print'])->name('patrol.checkpoints.print');
        Route::get('patrol-checkpoints-print-all', [CheckpointController::class, 'printAll'])->name('patrol.checkpoints.printAll');

        // Broadcast Notifications
        Route::get('broadcast', [\App\Http\Controllers\Admin\BroadcastController::class, 'index'])->name('broadcast.index');
        Route::get('broadcast/create', [\App\Http\Controllers\Admin\BroadcastController::class, 'create'])->name('broadcast.create');
        Route::post('broadcast', [\App\Http\Controllers\Admin\BroadcastController::class, 'store'])->name('broadcast.store');
        Route::get('broadcast/{broadcast}', [\App\Http\Controllers\Admin\BroadcastController::class, 'show'])->name('broadcast.show');
    });

    // HRD - hanya HRD
    Route::middleware(['role:SUPERADMIN,HRD'])->group(function (): void {
        Route::get('hrd/applications', [\App\Http\Controllers\Admin\JobApplicationController::class, 'index'])->name('hrd.applications');
        Route::get('hrd/rejected', [\App\Http\Controllers\Admin\JobApplicationController::class, 'index'])->name('hrd.rejected');
        Route::get('hrd/applications/{application}', [\App\Http\Controllers\Admin\JobApplicationController::class, 'show'])->name('hrd.applications.show');
        Route::put('hrd/applications/{application}/status', [\App\Http\Controllers\Admin\JobApplicationController::class, 'updateStatus'])->name('hrd.applications.status');
        Route::delete('hrd/applications/{application}', [\App\Http\Controllers\Admin\JobApplicationController::class, 'destroy'])->name('hrd.applications.destroy');

        // CV Routes
        Route::get('hrd/cv', [\App\Http\Controllers\Admin\CvController::class, 'index'])->name('hrd.cv.index');
        
        Route::get('hrd/cv/{user}', [\App\Http\Controllers\Admin\CvController::class, 'show'])->name('hrd.cv.show');
        Route::get('hrd/cv/{user}/pdf', [\App\Http\Controllers\Admin\CvController::class, 'exportPdf'])->name('hrd.cv.pdf');

        Route::get('pkwt', static function () {
            return view('admin.pkwt.index');
        })->name('pkwt.index');

        // Lowongan Kerja - HRD bisa akses
        Route::resource('cms-careers', \App\Http\Controllers\Admin\CmsCareerController::class)
            ->parameters(['cms-careers' => 'career'])
            ->except(['show']);
    });

    // Payroll - hanya PAYROLL
    Route::middleware(['role:SUPERADMIN,PAYROLL'])->group(function (): void {
        Route::get('payroll', [\App\Http\Controllers\Admin\PayrollController::class, 'index'])->name('payroll.index');
        Route::get('payroll/import', [\App\Http\Controllers\Admin\PayrollController::class, 'showImportForm'])->name('payroll.import.form');
        Route::post('payroll/import', [\App\Http\Controllers\Admin\PayrollController::class, 'import'])->name('payroll.import.store');
        Route::get('payroll/template', [\App\Http\Controllers\Admin\PayrollController::class, 'downloadTemplate'])->name('payroll.template');
        Route::match(['get', 'post'], 'payroll/print-bulk', [\App\Http\Controllers\Admin\PayrollController::class, 'printBulk'])->name('payroll.print-bulk');
        Route::post('payroll/send-bulk', [\App\Http\Controllers\Admin\PayrollController::class, 'sendBulk'])->name('payroll.send-bulk');
        Route::delete('payroll/period', [\App\Http\Controllers\Admin\PayrollController::class, 'destroyPeriod'])->name('payroll.destroy-period');
        Route::get('payroll/{slip}', [\App\Http\Controllers\Admin\PayrollController::class, 'show'])->name('payroll.show');
        Route::get('payroll/{slip}/print', [\App\Http\Controllers\Admin\PayrollController::class, 'print'])->name('payroll.print');
        Route::post('payroll/{slip}/send', [\App\Http\Controllers\Admin\PayrollController::class, 'send'])->name('payroll.send');
        Route::delete('payroll/{slip}', [\App\Http\Controllers\Admin\PayrollController::class, 'destroy'])->name('payroll.destroy');
    });

    // CMS - hanya CMS
    Route::middleware(['role:SUPERADMIN,CMS'])->group(function (): void {
        Route::resource('cms-contents', \App\Http\Controllers\Admin\CmsContentController::class)
            ->parameters(['cms-contents' => 'content'])
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
        Route::resource('cms-contacts', \App\Http\Controllers\Admin\ContactMessageController::class)
            ->parameters(['cms-contacts' => 'contactMessage'])
            ->only(['index', 'show', 'destroy']);

        // Testimonials
        Route::resource('cms-testimonials', \App\Http\Controllers\Admin\TestimonialController::class)
            ->parameters(['cms-testimonials' => 'testimonial'])
            ->except(['show']);
        Route::post('cms-testimonials/{testimonial}/approve', [\App\Http\Controllers\Admin\TestimonialController::class, 'approve'])->name('cms-testimonials.approve');
        Route::post('cms-testimonials/{testimonial}/reject', [\App\Http\Controllers\Admin\TestimonialController::class, 'reject'])->name('cms-testimonials.reject');
        Route::post('cms-testimonials-generate-link', [\App\Http\Controllers\Admin\TestimonialController::class, 'generateLink'])->name('cms-testimonials.generate-link');

        // FAQs
        Route::resource('cms-faqs', \App\Http\Controllers\Admin\FaqController::class)
            ->parameters(['cms-faqs' => 'faq'])
            ->except(['show']);

        // Settings
        Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

        // Applications
        Route::resource('cms-applications', \App\Http\Controllers\Admin\CmsApplicationController::class)
            ->parameters(['cms-applications' => 'cmsApplication'])
            ->except(['show']);
    });
});
