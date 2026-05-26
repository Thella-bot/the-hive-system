<?php

use App\Http\Controllers\Hive\Admin\ImportUsersController;
use App\Http\Controllers\Hive\AnnouncementController;
use App\Http\Controllers\Hive\AssignmentController;
use App\Http\Controllers\Hive\DashboardController;
use App\Http\Controllers\Hive\DocumentController;
use App\Http\Controllers\Hive\GradeController;
use App\Http\Controllers\Hive\LeaveRequestController;
use App\Http\Controllers\Hive\ModuleController;
use App\Http\Controllers\Hive\NotificationController;
use App\Http\Controllers\Hive\PayslipController;
use App\Http\Controllers\Hive\ProfileController;
use App\Http\Controllers\Hive\SubmissionController;
use App\Http\Controllers\Hive\TranscriptController;
use App\Http\Controllers\Hive\Admin\UserApprovalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->name('hive.')
    ->prefix('hive')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::middleware(['role:admin'])->name('admin.')->prefix('admin')->group(function () {
            Route::get('approve-users', [UserApprovalController::class, 'index'])->name('approve-users');
            Route::post('approve-users/{user}', [UserApprovalController::class, 'approve'])->name('approve-users.approve');
            Route::get('import-users', [ImportUsersController::class, 'show'])->name('import-users');
            Route::post('import-users', [ImportUsersController::class, 'import'])->name('import-users.store');
        });

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::resource('announcements', AnnouncementController::class)->except(['show']);
        Route::resource('assignments', AssignmentController::class)->only(['index', 'create', 'store', 'show']);
        Route::resource('documents', DocumentController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('documents/{document}/versions', [DocumentController::class, 'addVersion'])->name('documents.versions.store');
        Route::get('document-versions/{version}/download', [DocumentController::class, 'download'])->name('documents.version.download');

        Route::resource('leave-requests', LeaveRequestController::class)
            ->parameters(['leave-requests' => 'leave'])
            ->names('leaves');

        Route::get('modules', [ModuleController::class, 'index'])->name('modules.index');
        Route::post('programmes', [ModuleController::class, 'storeProgramme'])->name('programmes.store');
        Route::post('modules', [ModuleController::class, 'storeModule'])->name('modules.store');

        Route::get('grades', [GradeController::class, 'index'])->name('grades.index');
        Route::get('modules/{module}/grades', [GradeController::class, 'manage'])->name('grades.manage');
        Route::post('modules/{module}/grade-items', [GradeController::class, 'storeItem'])->name('grade-items.store');
        Route::post('grade-items/{gradeItem}/student-grades', [GradeController::class, 'storeStudentGrade'])->name('student-grades.store');

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');

        Route::get('payslips', [PayslipController::class, 'index'])->name('payslips.index');
        Route::get('payslips/create', [PayslipController::class, 'create'])->name('payslips.create');
        Route::post('payslips', [PayslipController::class, 'store'])->name('payslips.store');
        Route::get('payslips/{payslip}/download', [PayslipController::class, 'download'])->name('payslips.download');

        Route::post('assignments/{assignment}/submissions', [SubmissionController::class, 'store'])->name('submissions.store');
        Route::get('submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');
        Route::post('submissions/{submission}/grade', [SubmissionController::class, 'update'])->name('submissions.grade');

        Route::get('transcript', [TranscriptController::class, 'index'])->name('transcript.index');
    });
