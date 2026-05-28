<?php

use App\Http\Controllers\Hive\AcademicYearController;
use App\Http\Controllers\Hive\Admin\ImportUsersController;
use App\Http\Controllers\Hive\Admin\UserApprovalController;
use App\Http\Controllers\Hive\AnnouncementController;
use App\Http\Controllers\Hive\ApplicationController;
use App\Http\Controllers\Hive\CohortController;
use App\Http\Controllers\Hive\DashboardController;
use App\Http\Controllers\Hive\DepartmentController;
use App\Http\Controllers\Hive\DocumentController;
use App\Http\Controllers\Hive\EnrollmentController;
use App\Http\Controllers\Hive\EventController;
use App\Http\Controllers\Hive\GradableController;
use App\Http\Controllers\Hive\GradeController;
use App\Http\Controllers\Hive\LeaveRequestController;
use App\Http\Controllers\Hive\ModuleController;
use App\Http\Controllers\Hive\NotificationController;
use App\Http\Controllers\Hive\PayslipController;
use App\Http\Controllers\Hive\ProgrammeController;
use App\Http\Controllers\Hive\ProgrammeSoughtController;
use App\Http\Controllers\Hive\ProfileController;
use App\Http\Controllers\Hive\SearchController;
use App\Http\Controllers\Hive\StaffController;
use App\Http\Controllers\Hive\StudentController;
use App\Http\Controllers\Hive\SubmissionController;
use App\Http\Controllers\Hive\ChatController;
use App\Http\Controllers\Hive\TestController;
use App\Http\Controllers\Hive\TranscriptController;
use App\Http\Controllers\Hive\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->name('hive.')
    ->prefix('hive')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::middleware(['role:super-admin|school-admin'])->name('admin.')->prefix('admin')->group(function () {
            Route::get('approve-users', [UserApprovalController::class, 'index'])->name('approve-users');
            Route::post('approve-users/{user}', [UserApprovalController::class, 'approve'])->name('approve-users.approve');
            Route::get('import-users', [ImportUsersController::class, 'show'])->name('import-users');
            Route::post('import-users', [ImportUsersController::class, 'import'])->name('import-users.store');
        });

        Route::resource('users', UserController::class)->middleware('role:super-admin|school-admin');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::resource('announcements', AnnouncementController::class)->except(['show']);
        Route::resource('applications', ApplicationController::class)->except(['create', 'destroy']);
        Route::get('documents/modules', [DocumentController::class, 'moduleSelect'])->name('documents.module-select');
        Route::resource('documents', DocumentController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('documents/{document}/versions', [DocumentController::class, 'addVersion'])->name('documents.versions.store');
        Route::get('document-versions/{version}/download', [DocumentController::class, 'download'])->name('documents.version.download');

        Route::resource('leave-requests', LeaveRequestController::class)
            ->parameters(['leave-requests' => 'leave'])
            ->names('leaves')
            ->except(['create']);

        Route::get('modules', [ModuleController::class, 'index'])->name('modules.index');
        Route::post('programmes', [ModuleController::class, 'storeProgramme'])->name('programmes.store');
        Route::resource('modules', ModuleController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->middleware('role:super-admin|school-admin');

        Route::get('grades', [GradeController::class, 'index'])->name('grades.index');
        Route::get('modules/{module}/grades', [GradeController::class, 'manage'])->name('grades.manage');

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

        Route::get('payslips', [PayslipController::class, 'index'])->name('payslips.index');
        Route::get('payslips/create', [PayslipController::class, 'create'])->name('payslips.create');
        Route::post('payslips', [PayslipController::class, 'store'])->name('payslips.store');
        Route::get('payslips/{payslip}/edit', [PayslipController::class, 'edit'])->name('payslips.edit');
        Route::patch('payslips/{payslip}', [PayslipController::class, 'update'])->name('payslips.update');
        Route::delete('payslips/{payslip}', [PayslipController::class, 'destroy'])->name('payslips.destroy');
        Route::post('payslips/generate', [PayslipController::class, 'generate'])->name('payslips.generate');
        Route::post('payslips/generate-batch', [PayslipController::class, 'generateBatch'])->name('payslips.generate.batch');
        Route::get('payslips/{payslip}/download', [PayslipController::class, 'download'])->name('payslips.download');

        Route::get('submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');
        Route::post('submissions/{gradable}', [SubmissionController::class, 'store'])->name('submissions.store');
        Route::post('submissions/{submission}/grade', [SubmissionController::class, 'update'])->name('submissions.grade');

        Route::get('transcript', [TranscriptController::class, 'index'])->name('transcript.index');
        Route::get('transcript/{student}/download', [TranscriptController::class, 'show'])->name('transcript.download');

        // School Management
        Route::resource('departments', DepartmentController::class)->middleware('role:super-admin|school-admin');
        Route::resource('programmes', ProgrammeController::class)->middleware('role:super-admin|school-admin');
        Route::resource('cohorts', CohortController::class)->middleware('role:super-admin|school-admin');
        Route::resource('academic-years', AcademicYearController::class)->middleware('role:super-admin|school-admin');

        // People Management
        Route::resource('students', StudentController::class)->middleware('role:super-admin|school-admin');
        Route::resource('staff', StaffController::class)->middleware('role:super-admin|school-admin');

        // Academic Resources
        Route::get('gradables/{type}/modules', [GradableController::class, 'moduleSelect'])->name('gradables.module-select');
        Route::resource('gradables', GradableController::class)->only(['index', 'show']);
        Route::middleware('role:super-admin|school-admin|academic_staff')->group(function () {
            Route::resource('gradables', GradableController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        });

        // Enrollment (for students)
        Route::get('enrollment', [EnrollmentController::class, 'index'])->name('enrollment.index');
        Route::post('enrollment', [EnrollmentController::class, 'store'])->name('enrollment.store');
        Route::delete('enrollment/{module}', [EnrollmentController::class, 'destroy'])->name('enrollment.destroy');

        // Search
        Route::get('search', SearchController::class)->name('search');

        // Events Calendar
        Route::resource('events', EventController::class);

        // Tests
        Route::get('tests', [TestController::class, 'index'])->name('tests.index');

        // Chat
        Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
        Route::get('chat/{module}', [ChatController::class, 'show'])->name('chat.show');
    });