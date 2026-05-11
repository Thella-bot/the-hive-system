<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\Hive\AnnouncementController;
use App\Http\Controllers\Hive\AssignmentController;
use App\Http\Controllers\Hive\DocumentController;
use App\Http\Controllers\Hive\GradeController;
use App\Http\Controllers\Hive\ModuleController;
use App\Http\Controllers\Hive\SubmissionController;

use App\Http\Controllers\PublicController;

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // Admin module management
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
        Route::post('/programmes', [ModuleController::class, 'storeProgramme'])->name('programmes.store');
        Route::post('/modules', [ModuleController::class, 'storeModule'])->name('modules.store');
    });

    // Assignments
    Route::resource('assignments', AssignmentController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->names('hive.assignments');

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
        ->names('hive.documents');
    Route::post('/documents/{document}/versions', [DocumentController::class, 'addVersion'])
        ->name('documents.versions.store');
    Route::get('/documents/version/{version}/download', [DocumentController::class, 'download'])
        ->name('documents.version.download');

    // Announcements
    Route::resource('announcements', AnnouncementController::class)
        ->except(['show'])
        ->names('hive.announcements');
});
// Default dashboard route expected by Jetstream/Fortify tests
// (When authenticated via the web guard)
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->get('/dashboard', function () {
    // For this application, the hive dashboard is tenant-scoped (domain-based).
    // Redirect authenticated users to the hive home.
    return redirect()->route('hive.dashboard');
})->name('dashboard');

// Public website
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/programmes', [PublicController::class, 'programmes'])->name('programmes');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

