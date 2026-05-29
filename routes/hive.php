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
use App\Http\Controllers\Hive\RegistrationController;
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

            // Log Viewer
            Route::get('logs', fn() => redirect('/log-viewer'))->name('logs');
        });

        Route::resource('users', UserController::class)->middleware('role:super-admin|school-admin');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::resource('announcements', AnnouncementController::class)->except(['show']);
        Route::get('announcements/{announcement}/attachments/{attachment}/download', [AnnouncementController::class, 'downloadAttachment'])
            ->name('announcements.attachments.download');
        Route::resource('applications', ApplicationController::class)->except(['create', 'destroy']);
        Route::post('applications/{application}/complete-registration', [ApplicationController::class, 'completeRegistration'])->name('applications.complete-registration');

        // Registration (for admitted students)
        Route::get('registration', [RegistrationController::class, 'index'])->name('registration.index');
        Route::post('registration', [RegistrationController::class, 'store'])->name('registration.store');
        Route::patch('registration/{application}', [RegistrationController::class, 'update'])->name('registration.update')->middleware('role:super-admin|school-admin|non_academic_staff');
        Route::get('registration/proof', [RegistrationController::class, 'downloadProof'])->name('registration.proof')->middleware('registered');
        Route::resource('documents', DocumentController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::get('documents/modules', [DocumentController::class, 'moduleSelect'])->name('documents.module-select');
        Route::post('documents/{document}/versions', [DocumentController::class, 'addVersion'])->name('documents.versions.store');
        Route::get('document-versions/{version}/download', [DocumentController::class, 'download'])->name('documents.version.download');
        Route::post('documents/{document}/acknowledge', [DocumentController::class, 'acknowledge'])->name('documents.acknowledge');
        Route::get('documents/{document}/acknowledgements', [DocumentController::class, 'acknowledgements'])->name('documents.acknowledgements');

        Route::resource('leave-requests', LeaveRequestController::class)
            ->parameters(['leave-requests' => 'leave'])
            ->names('leaves')
            ->except(['create']);

        Route::get('leave-requests/create', [LeaveRequestController::class, 'create'])->name('leaves.create');

        Route::get('modules', [ModuleController::class, 'index'])->name('modules.index');
        Route::post('programmes', [ModuleController::class, 'storeProgramme'])->name('programmes.store');
        Route::resource('modules', ModuleController::class)->only(['create', 'store', 'edit', 'update', 'destroy'])->middleware('role:super-admin|school-admin');

        Route::get('grades', [GradeController::class, 'index'])->name('grades.index')->middleware('registered');
        Route::get('modules/{module}/grades', [GradeController::class, 'manage'])->name('grades.manage')->middleware('registered');

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

        Route::get('submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download')->middleware('registered');
        Route::post('submissions/{gradable}', [SubmissionController::class, 'store'])->name('submissions.store')->middleware('registered');
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
        Route::resource('gradables', GradableController::class)->only(['index', 'show'])->middleware('registered');
        Route::middleware('role:super-admin|school-admin|academic_staff')->group(function () {
            Route::resource('gradables', GradableController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
            // Attachment routes
            Route::post('gradables/{gradable}/attachments', [GradableController::class, 'uploadAttachment'])->name('gradables.attachments.store');
            Route::delete('gradables/{gradable}/attachments/{attachment}', [GradableController::class, 'deleteAttachment'])->name('gradables.attachments.destroy');
            Route::get('gradables/{gradable}/attachments/{attachment}/download', [GradableController::class, 'downloadAttachment'])->name('gradables.attachments.download');
            // Question routes
            Route::post('gradables/{gradable}/questions', [GradableController::class, 'storeQuestion'])->name('gradables.questions.store');
            Route::put('gradables/{gradable}/questions/{question}', [GradableController::class, 'updateQuestion'])->name('gradables.questions.update');
            Route::delete('gradables/{gradable}/questions/{question}', [GradableController::class, 'deleteQuestion'])->name('gradables.questions.destroy');
        });

        // Student answer submission for online assessments (requires registered middleware)
        Route::post('gradables/{gradable}/submit-online', [GradableController::class, 'submitOnline'])->name('gradables.submit-online')->middleware('registered');

        // Enrollment (for students)
        Route::get('enrollment', [EnrollmentController::class, 'index'])->name('enrollment.index')->middleware('registered');
        Route::post('enrollment', [EnrollmentController::class, 'store'])->name('enrollment.store')->middleware('registered');
        Route::delete('enrollment/{module}', [EnrollmentController::class, 'destroy'])->name('enrollment.destroy')->middleware('registered');

        // Search
        Route::get('search', SearchController::class)->name('search');

        // Events Calendar
        Route::resource('events', EventController::class);
        Route::post('events/{event}/rsvp', [EventController::class, 'rsvp'])->name('events.rsvp');
        Route::get('events/{event}/ical', [EventController::class, 'exportICal'])->name('events.ical');
        Route::get('events/{event}/qrcode', [EventController::class, 'qrCode'])->name('events.qrcode');

        // Assessments - redirect tests to gradables
        Route::redirect('tests', 'gradables?type=test')->name('tests.index');

        // Chat
        Route::get('chat', [ChatController::class, 'index'])->name('chat.index')->middleware('registered');
        Route::get('chat/channel/{channel}', [ChatController::class, 'showChannel'])->name('chat.channel')->middleware('registered');
        Route::get('chat/module/{module}', [ChatController::class, 'showModule'])->name('chat.module')->middleware('registered');

        // Achievements (leaderboard)
        Route::resource('achievements', AchievementController::class)->only(['index', 'store', 'destroy']);

        // Polls & surveys
        Route::resource('polls', PollController::class)->only(['index', 'store', 'destroy']);
        Route::post('polls/{poll}/vote', [PollController::class, 'vote'])->name('polls.vote');

        // Programme waitlist
        Route::get('waitlist', [WaitlistController::class, 'index'])->name('waitlist.index');
        Route::post('waitlist', [WaitlistController::class, 'join'])->name('waitlist.join');
        Route::delete('waitlist/{waitlist}', [WaitlistController::class, 'leave'])->name('waitlist.leave');

        // Keys & access management
        Route::resource('keys', KeyController::class)->only(['index', 'store']);
        Route::post('keys/{key}/issue', [KeyController::class, 'issue'])->name('keys.issue');
        Route::post('keys/{key}/return', [KeyController::class, 'return'])->name('keys.return');
        Route::post('keys/{key}/report-lost', [KeyController::class, 'reportLost'])->name('keys.report-lost');

        // Attendance / QR check-in
        Route::get('attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');
        Route::post('attendance/checkin', [AttendanceController::class, 'checkin'])->name('attendance.checkin');

        // Visitor log
        Route::resource('visitor-logs', VisitorLogController::class)->only(['index', 'store']);
        Route::post('visitor-logs/{log}/checkout', [VisitorLogController::class, 'checkOut'])->name('visitor-logs.checkout');

        // Suppliers
        Route::resource('suppliers', SupplierController::class);

        // Uniform requests
        Route::resource('uniform-requests', UniformRequestController::class)->only(['index', 'store']);
        Route::post('uniform-requests/{uniform_request}/review', [UniformRequestController::class, 'review'])->name('uniform-requests.review');

        // Student ID card
        Route::get('student-id', [StudentIdController::class, 'show'])->name('student-id');
    });