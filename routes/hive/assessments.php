<?php

use App\Http\Controllers\Hive\GradableController;
use App\Http\Controllers\Hive\GradeController;
use App\Http\Controllers\Hive\SubmissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Assessment Routes
|--------------------------------------------------------------------------
|
| Routes for managing gradables (quizzes, tests, assignments, exams),
| grades, and submissions.
|
*/

// Module selection for gradables
Route::get('gradables/{type}/modules', [GradableController::class, 'moduleSelect'])
    ->name('gradables.module-select');

// Public listing (filtered by role via controller)
Route::resource('gradables', GradableController::class)->only(['index', 'show'])
    ->middleware('registered');

// Staff-only gradable management
Route::middleware(['role:super-admin|school-admin|academic_staff'])->group(function () {
    Route::resource('gradables', GradableController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);

    // Attachment routes
    Route::post('gradables/{gradable}/attachments', [GradableController::class, 'uploadAttachment'])
        ->name('gradables.attachments.store');
    Route::delete('gradables/{gradable}/attachments/{attachment}', [GradableController::class, 'deleteAttachment'])
        ->name('gradables.attachments.destroy');
    Route::get('gradables/{gradable}/attachments/{attachment}/download', [GradableController::class, 'downloadAttachment'])
        ->name('gradables.attachments.download');

    // Question routes
    Route::post('gradables/{gradable}/questions', [GradableController::class, 'storeQuestion'])
        ->name('gradables.questions.store');
    Route::put('gradables/{gradable}/questions/{question}', [GradableController::class, 'updateQuestion'])
        ->name('gradables.questions.update');
    Route::delete('gradables/{gradable}/questions/{question}', [GradableController::class, 'deleteQuestion'])
        ->name('gradables.questions.destroy');
});

// Student submission for online assessments
Route::post('gradables/{gradable}/submit-online', [GradableController::class, 'submitOnline'])
    ->name('gradables.submit-online')
    ->middleware('registered');

// Submission download and grading
Route::get('submissions/{submission}/download', [SubmissionController::class, 'download'])
    ->name('submissions.download')
    ->middleware('registered');
Route::post('submissions/{gradable}', [SubmissionController::class, 'store'])
    ->name('submissions.store')
    ->middleware('registered');
Route::post('submissions/{submission}/grade', [SubmissionController::class, 'update'])
    ->name('submissions.grade');

// Grades
Route::get('grades', [GradeController::class, 'index'])->name('grades.index')
    ->middleware('registered');
Route::get('modules/{module}/grades', [GradeController::class, 'manage'])->name('grades.manage')
    ->middleware('registered');

// Redirect tests to gradables
Route::redirect('tests', 'gradables?type=test')->name('tests.index');
