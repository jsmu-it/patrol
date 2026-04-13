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

use App\Http\Controllers\RegionalController;

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

// Public Permission Request (no auth required)
Route::get('/permission', [\App\Http\Controllers\PublicPermissionController::class, 'create'])->name('public.permissions.create');
Route::post('/permission', [\App\Http\Controllers\PublicPermissionController::class, 'store'])->name('public.permissions.store');

// Public Meeting Room (no auth required)
Route::get('/meeting/{slug}', [\App\Http\Controllers\MeetingController::class, 'join'])->name('meeting.join');
Route::post('/meeting/{meeting}/token', [\App\Http\Controllers\Admin\MeetingController::class, 'generateToken'])->name('meeting.token');

// Psikotest Public Routes (no auth required)
Route::prefix('psikotest')->name('psikotest.')->group(function (): void {
    Route::get('/', [\App\Http\Controllers\PsikotestTakeController::class, 'index'])->name('home');
    Route::post('/validate', [\App\Http\Controllers\PsikotestTakeController::class, 'validateToken'])->name('validate');
    Route::get('/take/{token}', [\App\Http\Controllers\PsikotestTakeController::class, 'take'])->name('take');
    Route::post('/submit/{token}', [\App\Http\Controllers\PsikotestTakeController::class, 'submit'])->name('submit');
});

// Form testimoni publik (via link)
Route::get('/testimonial/{token}', [\App\Http\Controllers\TestimonialFormController::class, 'showForm'])->name('testimonial.form');
Route::post('/testimonial/{token}', [\App\Http\Controllers\TestimonialFormController::class, 'submitForm'])->name('testimonial.submit');

// Form PDP untuk karyawan (publik)
Route::get('/pdp', [EmployeeProfileController::class, 'showForm'])->name('pdp.form');
Route::post('/pdp', [EmployeeProfileController::class, 'submitForm'])->name('pdp.submit');

// API Wilayah Indonesia (untuk dropdown alamat)
Route::get('/api/regional/provinces', [RegionalController::class, 'getProvinces'])->name('regional.provinces');
Route::get('/api/regional/regencies/{provinceId}', [RegionalController::class, 'getRegencies'])->name('regional.regencies');
Route::get('/api/regional/districts/{regencyId}', [RegionalController::class, 'getDistricts'])->name('regional.districts');
Route::get('/api/regional/villages/{districtId}', [RegionalController::class, 'getVillages'])->name('regional.villages');

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

        // Permission Management - untuk mengatur admin dan otoritas project
        Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class)
            ->except(['show']);
    });

    // Dashboard - ADMIN, PROJECT_ADMIN, HRD
    Route::middleware(['role:SUPERADMIN,ADMIN,PROJECT_ADMIN,HRD'])->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show']);
        Route::get('users-import', [\App\Http\Controllers\Admin\UserController::class, 'showImportForm'])->name('users.import.form');
        Route::post('users-import', [\App\Http\Controllers\Admin\UserController::class, 'import'])->name('users.import.store');
        Route::get('users-import-template', [\App\Http\Controllers\Admin\UserController::class, 'downloadImportTemplate'])->name('users.import.template');
        Route::get('users-export', [\App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export');

        Route::resource('projects', ProjectController::class)->except(['show']);
        Route::get('projects/{project}/shifts', [ProjectController::class, 'editShifts'])->name('projects.shifts.edit');
        Route::post('projects/{project}/shifts', [ProjectController::class, 'updateShifts'])->name('projects.shifts.update');
        
        Route::resource('shifts', \App\Http\Controllers\Admin\ShiftController::class)->except(['show']);

        Route::get('projects/{project}/pkwt', [ProjectController::class, 'editPkwt'])->name('projects.pkwt.edit');
        Route::put('projects/{project}/pkwt', [ProjectController::class, 'updatePkwt'])->name('projects.pkwt.update');

        Route::get('reports/attendance', [AttendanceReportController::class, 'index'])->name('reports.attendance');
        Route::get('reports/attendance/export-excel', [AttendanceReportController::class, 'exportExcel'])->name('reports.attendance.exportExcel');
        Route::get('reports/attendance/export-pdf', [AttendanceReportController::class, 'exportPdf'])->name('reports.attendance.exportPdf');
        Route::get('reports/attendance/download-user', [AttendanceReportController::class, 'downloadUserAttendance'])->name('reports.attendance.downloadUser');

        Route::get('reports/performance', [AttendanceReportController::class, 'performance'])->name('reports.performance');


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
        Route::get('patrol-checkpoints-import', [CheckpointController::class, 'showImportForm'])->name('patrol.checkpoints.import.form');
        Route::post('patrol-checkpoints-import', [CheckpointController::class, 'import'])->name('patrol.checkpoints.import.store');
        Route::get('patrol-checkpoints-template', [CheckpointController::class, 'downloadTemplate'])->name('patrol.checkpoints.template');
        Route::get('patrol-checkpoints/{checkpoint}/print', [CheckpointController::class, 'print'])->name('patrol.checkpoints.print');
        Route::get('patrol-checkpoints-print-all', [CheckpointController::class, 'printAll'])->name('patrol.checkpoints.printAll');

        // Broadcast Notifications (Pemberitahuan)
        Route::get('broadcast', [\App\Http\Controllers\Admin\BroadcastController::class, 'index'])->name('broadcast.index');
        Route::get('broadcast/create', [\App\Http\Controllers\Admin\BroadcastController::class, 'create'])->name('broadcast.create');
        Route::post('broadcast', [\App\Http\Controllers\Admin\BroadcastController::class, 'store'])->name('broadcast.store');
        Route::get('broadcast/{broadcast}', [\App\Http\Controllers\Admin\BroadcastController::class, 'show'])->name('broadcast.show');
        Route::delete('broadcast/{broadcast}', [\App\Http\Controllers\Admin\BroadcastController::class, 'destroy'])->name('broadcast.destroy');

        // Meeting (Video Conference)
        Route::resource('meetings', \App\Http\Controllers\Admin\MeetingController::class)->except(['show']);
        Route::post('meetings/{meeting}/toggle-status', [\App\Http\Controllers\Admin\MeetingController::class, 'toggleStatus'])->name('meetings.toggle-status');
        Route::get('meetings/{meeting}/room', [\App\Http\Controllers\Admin\MeetingController::class, 'room'])->name('meetings.room');
        Route::post('meetings/{meeting}/end', [\App\Http\Controllers\Admin\MeetingController::class, 'endMeeting'])->name('meetings.end');

        // Leave Types Management
        Route::get('leave-types', [\App\Http\Controllers\Admin\LeaveTypeController::class, 'index'])->name('leave-types.index');
        Route::post('leave-types', [\App\Http\Controllers\Admin\LeaveTypeController::class, 'store'])->name('leave-types.store');
        Route::put('leave-types/{leaveType}', [\App\Http\Controllers\Admin\LeaveTypeController::class, 'update'])->name('leave-types.update');
        Route::delete('leave-types/{leaveType}', [\App\Http\Controllers\Admin\LeaveTypeController::class, 'destroy'])->name('leave-types.destroy');
        Route::get('leave-types/{leaveType}/assign', [\App\Http\Controllers\Admin\LeaveTypeController::class, 'showAssign'])->name('leave-types.assign');
        Route::post('leave-types/{leaveType}/assign', [\App\Http\Controllers\Admin\LeaveTypeController::class, 'assign']);

        // Leave Balance Management
        Route::get('leave-balance', [\App\Http\Controllers\Admin\LeaveBalanceController::class, 'index'])->name('leave-balance.index');
        Route::get('leave-balance/{user}', [\App\Http\Controllers\Admin\LeaveBalanceController::class, 'show'])->name('leave-balance.show');
        Route::put('leave-balance/{user}', [\App\Http\Controllers\Admin\LeaveBalanceController::class, 'update'])->name('leave-balance.update');
        Route::post('leave-balance/reset-all', [\App\Http\Controllers\Admin\LeaveBalanceController::class, 'resetAll'])->name('leave-balance.reset-all');
        Route::post('leave-balance/bulk-update', [\App\Http\Controllers\Admin\LeaveBalanceController::class, 'bulkUpdate'])->name('leave-balance.bulk-update');
        Route::post('leave-balance/bulk-reset', [\App\Http\Controllers\Admin\LeaveBalanceController::class, 'bulkReset'])->name('leave-balance.bulk-reset');
        Route::post('leave-balance/bulk-assign', [\App\Http\Controllers\Admin\LeaveBalanceController::class, 'bulkAssign'])->name('leave-balance.bulk-assign');
    });

    // HRD - HRD & PAYROLL
    Route::middleware(['role:SUPERADMIN,HRD,PAYROLL'])->group(function (): void {
        Route::get('hrd/applications', [\App\Http\Controllers\Admin\JobApplicationController::class, 'index'])->name('hrd.applications');
        Route::get('hrd/rejected', [\App\Http\Controllers\Admin\JobApplicationController::class, 'index'])->name('hrd.rejected');
        Route::get('hrd/applications/{application}', [\App\Http\Controllers\Admin\JobApplicationController::class, 'show'])->name('hrd.applications.show');
        Route::put('hrd/applications/{application}/status', [\App\Http\Controllers\Admin\JobApplicationController::class, 'updateStatus'])->name('hrd.applications.status');
        Route::delete('hrd/applications/{application}', [\App\Http\Controllers\Admin\JobApplicationController::class, 'destroy'])->name('hrd.applications.destroy');

        // CV Routes
        Route::get('hrd/cv', [\App\Http\Controllers\Admin\CvController::class, 'index'])->name('hrd.cv.index');
        
        Route::get('hrd/cv/{user}', [\App\Http\Controllers\Admin\CvController::class, 'show'])->name('hrd.cv.show');
        Route::get('hrd/cv/{user}/pdf', [\App\Http\Controllers\Admin\CvController::class, 'exportPdf'])->name('hrd.cv.pdf');

        Route::get('pkwt', [\App\Http\Controllers\Admin\PkwtController::class, 'index'])->name('pkwt.index');
        Route::put('pkwt/{pkwt}', [\App\Http\Controllers\Admin\PkwtController::class, 'update'])->name('pkwt.update');
        Route::post('pkwt/{pkwt}/activate', [\App\Http\Controllers\Admin\PkwtController::class, 'activate'])->name('pkwt.activate');
        Route::get('pkwt/{pkwt}/preview', [\App\Http\Controllers\Admin\PkwtController::class, 'preview'])->name('pkwt.preview');
        Route::post('pkwt/{pkwt}/send', [\App\Http\Controllers\Admin\PkwtController::class, 'send'])->name('pkwt.send');
        Route::get('pkwt/{pkwt}/print', [\App\Http\Controllers\Admin\PkwtController::class, 'print'])->name('pkwt.print');
        Route::delete('pkwt/{pkwt}', [\App\Http\Controllers\Admin\PkwtController::class, 'destroy'])->name('pkwt.destroy');
        Route::delete('pkwt-bulk', [\App\Http\Controllers\Admin\PkwtController::class, 'bulkDelete'])->name('pkwt.bulkDelete');
        
        // Quick add types for PKWT
        Route::post('pkwt-income-types', [\App\Http\Controllers\Admin\PkwtController::class, 'storeIncomeType'])->name('pkwt.income-types.store');
        Route::post('pkwt-deduction-types', [\App\Http\Controllers\Admin\PkwtController::class, 'storeDeductionType'])->name('pkwt.deduction-types.store');
        Route::post('pkwt-positions', [\App\Http\Controllers\Admin\PkwtController::class, 'storePosition'])->name('pkwt.positions.store');

        // Lowongan Kerja - HRD bisa akses
        Route::resource('cms-careers', \App\Http\Controllers\Admin\CmsCareerController::class)
            ->parameters(['cms-careers' => 'career'])
            ->except(['show']);

        // Psikotest Routes
        Route::prefix('psikotest')->name('psikotest.')->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Admin\PsikotestController::class, 'index'])->name('index');
            
            // Kraepelin
            Route::resource('kraepelin', \App\Http\Controllers\Admin\KraepelinController::class)
                ->parameters(['kraepelin' => 'kraepelin']);
            Route::post('kraepelin/{kraepelin}/regenerate', [\App\Http\Controllers\Admin\KraepelinController::class, 'regenerate'])->name('kraepelin.regenerate');
            
            // Papikostik
            Route::resource('papikostik', \App\Http\Controllers\Admin\PapikostikController::class)
                ->parameters(['papikostik' => 'papikostik']);
            Route::post('papikostik/generate-standard', [\App\Http\Controllers\Admin\PapikostikController::class, 'generateStandard'])->name('papikostik.generate');
            
            // Dashboard & Results
            Route::get('dashboard', [\App\Http\Controllers\Admin\PsikotestResultController::class, 'index'])->name('dashboard');
            Route::get('result/{session}', [\App\Http\Controllers\Admin\PsikotestResultController::class, 'show'])->name('result.show');
            Route::get('result/{session}/pdf', [\App\Http\Controllers\Admin\PsikotestResultController::class, 'exportPdf'])->name('result.pdf');
            
            // Sessions (Invitations)
            Route::get('sessions/create', [\App\Http\Controllers\Admin\PsikotestSessionController::class, 'create'])->name('sessions.create');
            Route::post('sessions', [\App\Http\Controllers\Admin\PsikotestSessionController::class, 'store'])->name('sessions.store');
            Route::get('sessions/{session}', [\App\Http\Controllers\Admin\PsikotestSessionController::class, 'show'])->name('sessions.show');
            Route::delete('sessions/{session}', [\App\Http\Controllers\Admin\PsikotestSessionController::class, 'destroy'])->name('sessions.destroy');
        });
    });

    // Payroll - PAYROLL & HRD
    Route::middleware(['role:SUPERADMIN,PAYROLL,HRD'])->group(function (): void {
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

// Public File Sharing (no auth required)
Route::prefix('sharing')->name('sharing.')->group(function () {
    Route::get('/', [\App\Http\Controllers\SharingController::class, 'index'])->name('index');
    Route::post('/folder', [\App\Http\Controllers\SharingController::class, 'createFolder'])->name('folder');
    Route::post('/upload', [\App\Http\Controllers\SharingController::class, 'upload'])->name('upload');
    Route::get('/{sharing}/download', [\App\Http\Controllers\SharingController::class, 'download'])->name('download');
    Route::post('/{sharing}/unlock', [\App\Http\Controllers\SharingController::class, 'unlockFolder'])->name('unlock');

    // Delete requires authentication
    Route::middleware(['auth'])->group(function () {
        Route::delete('/{sharing}', [\App\Http\Controllers\SharingController::class, 'destroy'])->name('destroy');
    });
});

// PWA Mobile Web App Routes
Route::prefix('app')->name('pwa.')->group(function (): void {
    Route::get('/', [\App\Http\Controllers\Pwa\PwaController::class, 'login'])->name('login');
    Route::get('/home', [\App\Http\Controllers\Pwa\PwaController::class, 'home'])->name('home');
    Route::get('/attendance', [\App\Http\Controllers\Pwa\PwaController::class, 'attendance'])->name('attendance');
    Route::get('/patrol', [\App\Http\Controllers\Pwa\PwaController::class, 'patrolScan'])->name('patrol.scan');
    Route::get('/patrol/form', [\App\Http\Controllers\Pwa\PwaController::class, 'patrolForm'])->name('patrol.form');
    Route::get('/leave', [\App\Http\Controllers\Pwa\PwaController::class, 'leaveList'])->name('leave.list');
    Route::get('/leave/create', [\App\Http\Controllers\Pwa\PwaController::class, 'leaveForm'])->name('leave.form');
    Route::get('/approvals/leave', [\App\Http\Controllers\Pwa\PwaController::class, 'approvalLeave'])->name('approvals.leave');
    Route::get('/history', [\App\Http\Controllers\Pwa\PwaController::class, 'history'])->name('history');
    Route::get('/profile', [\App\Http\Controllers\Pwa\PwaController::class, 'profile'])->name('profile');
});
