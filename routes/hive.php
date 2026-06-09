<?php

use App\Http\Controllers\Hive\AnnouncementController;
use App\Http\Controllers\Hive\ApplicationController;
use App\Http\Controllers\Hive\AcademicYearController;
use App\Http\Controllers\Hive\AttendanceController;
use App\Http\Controllers\Hive\AchievementController;
use App\Http\Controllers\Hive\ChatController;
use App\Http\Controllers\Hive\CohortController;
use App\Http\Controllers\Hive\DashboardController;
use App\Http\Controllers\Hive\DepartmentController;
use App\Http\Controllers\Hive\DocumentController;
use App\Http\Controllers\Hive\ProgrammeController;
use App\Http\Controllers\Hive\EnrollmentController;
use App\Http\Controllers\Hive\EventController;
use App\Http\Controllers\Hive\KeyController;
use App\Http\Controllers\Hive\LeaveRequestController;
use App\Http\Controllers\Hive\ModuleController;
use App\Http\Controllers\Hive\NotificationController;
use App\Http\Controllers\Hive\PayslipController;
use App\Http\Controllers\Hive\PollController;
use App\Http\Controllers\Hive\ProfileController;
use App\Http\Controllers\Hive\RegistrationController;
use App\Http\Controllers\Hive\SearchController;
use App\Http\Controllers\Hive\ShortCourseController;
use App\Http\Controllers\Hive\ShortCourseApplicationController;
use App\Http\Controllers\Hive\StudentIdController;
use App\Http\Controllers\Hive\SubmissionController;
use App\Http\Controllers\Hive\SupplierController;
use App\Http\Controllers\Hive\TranscriptController;
use App\Http\Controllers\Hive\UniformRequestController;
use App\Http\Controllers\Hive\VisitorLogController;
use App\Http\Controllers\Hive\WaitlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Hive Routes
|--------------------------------------------------------------------------
|
| Main application routes for The Hive intranet system.
| Routes are organized into logical groups included below.
|
*/

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->name('hive.')
    ->prefix('hive')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Departments
        Route::resource('departments', DepartmentController::class)
            ->middleware('role:super-admin|school-admin');

        // Programmes
        Route::resource('programmes', ProgrammeController::class)
            ->middleware('role:super-admin|school-admin');

        // Short Courses
        Route::resource('short-courses', ShortCourseController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->middleware('role:super-admin|school-admin');

        // Short Course Applications
        Route::get('short-course-applications', [ShortCourseApplicationController::class, 'index'])
            ->name('short-course-applications.index')
            ->middleware('role:super-admin|school-admin');
        Route::post('short-course-applications/{short_course_application}/review', [ShortCourseApplicationController::class, 'review'])
            ->name('short-course-applications.review')
            ->middleware('role:super-admin|school-admin');

        // Cohorts
        Route::resource('cohorts', CohortController::class)
            ->middleware('role:super-admin|school-admin');

        // Academic Years
        Route::resource('academic-years', AcademicYearController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->middleware('role:super-admin|school-admin');

        // Include modular route files
        require __DIR__ . '/hive/people.php';
        require __DIR__ . '/hive/academic.php';
        require __DIR__ . '/hive/assessments.php';

        // Profile
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');

        // Announcements
        Route::resource('announcements', AnnouncementController::class)->except(['destroy']);
        Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
        Route::get('announcements/{announcement}/attachments/{attachment}/download', [AnnouncementController::class, 'downloadAttachment'])
            ->name('announcements.attachments.download');

        // Applications
        Route::resource('applications', ApplicationController::class)->except(['create', 'destroy']);
        Route::post('applications/{application}/complete-registration', [ApplicationController::class, 'completeRegistration'])
            ->name('applications.complete-registration');

        // Registration (for admitted students)
        Route::get('registration', [RegistrationController::class, 'index'])->name('registration.index');
        Route::post('registration', [RegistrationController::class, 'store'])->name('registration.store');
        Route::patch('registration/{application}', [RegistrationController::class, 'update'])
            ->name('registration.update')
            ->middleware('role:super-admin|school-admin|non_academic_staff');
        Route::get('registration/proof', [RegistrationController::class, 'downloadProof'])
            ->name('registration.proof')
            ->middleware('registered');

        // Documents
        Route::resource('documents', DocumentController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        Route::get('documents/modules', [DocumentController::class, 'moduleSelect'])->name('documents.module-select');
        Route::post('documents/{document}/versions', [DocumentController::class, 'addVersion'])->name('documents.versions.store');
        Route::get('document-versions/{version}/download', [DocumentController::class, 'download'])->name('documents.version.download');
        Route::post('documents/{document}/acknowledge', [DocumentController::class, 'acknowledge'])->name('documents.acknowledge');
        Route::get('documents/{document}/acknowledgements', [DocumentController::class, 'acknowledgements'])->name('documents.acknowledgements');

        // Leave Requests
        Route::resource('leave-requests', LeaveRequestController::class)
            ->parameters(['leave-requests' => 'leave'])
            ->names('leaves')
            ->only(['index', 'store', 'update', 'destroy']);
        Route::get('leave-requests/create', [LeaveRequestController::class, 'create'])->name('leaves.create');

        // Modules
        Route::get('modules', [ModuleController::class, 'index'])->name('modules.index');
        Route::post('programmes', [ModuleController::class, 'storeProgramme'])->name('programmes.store');
        Route::resource('modules', ModuleController::class)->only(['create', 'store', 'edit', 'update', 'destroy'])
            ->middleware('role:super-admin|school-admin');

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

        // Payslips
        Route::get('payslips', [PayslipController::class, 'index'])->name('payslips.index');
        Route::get('payslips/create', [PayslipController::class, 'create'])->name('payslips.create');
        Route::post('payslips', [PayslipController::class, 'store'])->name('payslips.store');
        Route::get('payslips/{payslip}/edit', [PayslipController::class, 'edit'])->name('payslips.edit');
        Route::patch('payslips/{payslip}', [PayslipController::class, 'update'])->name('payslips.update');
        Route::delete('payslips/{payslip}', [PayslipController::class, 'destroy'])->name('payslips.destroy');
        Route::post('payslips/generate', [PayslipController::class, 'generate'])->name('payslips.generate');
        Route::post('payslips/generate-batch', [PayslipController::class, 'generateBatch'])->name('payslips.generate.batch');
        Route::get('payslips/{payslip}/download', [PayslipController::class, 'download'])->name('payslips.download');

        // Transcript
        Route::get('transcript', [TranscriptController::class, 'index'])->name('transcript.index');
        Route::get('transcript/{student}/download', [TranscriptController::class, 'show'])->name('transcript.download');

        // Enrollment (for students)
        Route::get('enrollment', [EnrollmentController::class, 'index'])->name('enrollment.index')
            ->middleware('registered');
        Route::post('enrollment', [EnrollmentController::class, 'store'])->name('enrollment.store')
            ->middleware('registered');
        Route::delete('enrollment/{module}', [EnrollmentController::class, 'destroy'])->name('enrollment.destroy')
            ->middleware('registered');

        // Search
        Route::get('search', SearchController::class)->name('search');

        // Events Calendar
        Route::resource('events', EventController::class);
        Route::post('events/{event}/rsvp', [EventController::class, 'rsvp'])->name('events.rsvp');
        Route::get('events/{event}/ical', [EventController::class, 'exportICal'])->name('events.ical');
        Route::get('events/{event}/qrcode', [EventController::class, 'qrCode'])->name('events.qrcode');

        // Chat
        Route::get('chat', [ChatController::class, 'index'])->name('chat.index')
            ->middleware('registered');
        Route::get('chat/channel/{channel}', [ChatController::class, 'showChannel'])->name('chat.channel')
            ->middleware('registered');
        Route::get('chat/module/{module}', [ChatController::class, 'showModule'])->name('chat.module')
            ->middleware('registered');

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
        Route::resource('suppliers', SupplierController::class)->only(['index', 'store', 'update', 'destroy']);

        // Uniform requests
        Route::resource('uniform-requests', UniformRequestController::class)->only(['index', 'store']);
        Route::post('uniform-requests/{uniform_request}/review', [UniformRequestController::class, 'review'])->name('uniform-requests.review');

        // Student ID card
        Route::get('student-id', [StudentIdController::class, 'show'])->name('student-id');
    });
