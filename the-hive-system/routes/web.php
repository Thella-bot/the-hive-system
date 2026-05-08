<?php

use App\Http\Controllers\Intranet\AnnouncementController;
use App\Http\Controllers\Intranet\AssignmentController;
use App\Http\Controllers\Intranet\DocumentController;
use App\Http\Controllers\Intranet\GradeController;
use App\Http\Controllers\Intranet\ModuleController;
use App\Http\Controllers\Intranet\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // Admin module management
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::post('/programmes', [ModuleController::class, 'storeProgramme'])->name('programmes.store');
    Route::post('/modules', [ModuleController::class, 'storeModule'])->name('modules.store');
    });

    // Assignments (accessible by instructors, admin, and students)
    Route::resource('assignments', AssignmentController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->names('intranet.assignments');

    // Submission
    Route::post('/assignments/{assignment}/submit', [SubmissionController::class, 'store'])->name('submissions.store');
    Route::post('/submissions/{submission}/grade', [SubmissionController::class, 'update'])->name('submissions.grade');
    Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');

    // Grades
    Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');
    Route::get('/grades/manage/{module}', [GradeController::class, 'manage'])->name('grades.manage');
    Route::post('/grades/items/{module}', [GradeController::class, 'storeItem'])->name('grades.items.store');
    Route::post('/grades/student/{gradeItem}', [GradeController::class, 'storeStudentGrade'])->name('grades.student.store');

    // Documents
    Route::resource('documents', DocumentController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->names('intranet.documents');
    Route::post('/documents/{document}/versions', [DocumentController::class, 'addVersion'])
        ->name('documents.versions.store');
    Route::get('/documents/version/{version}/download', [DocumentController::class, 'download'])
        ->name('documents.version.download');

    // Announcements
    Route::resource('announcements', AnnouncementController::class)->names('intranet.announcements');
});
// Public website
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/programmes', [PublicController::class, 'programmes'])->name('programmes');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
