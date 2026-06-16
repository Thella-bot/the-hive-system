<?php

use App\Http\Controllers\Hive\EnrollmentController;
use App\Http\Controllers\Hive\RegistrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Registrar Management Routes
|--------------------------------------------------------------------------
|
| Routes for managing student registrations and enrollments (registrar functionality).
|
*/

// Registrar routes (super-admin, registrar, program-coordinator)
Route::middleware(['role:super-admin|registrar|program-coordinator'])->name('registrar.')->prefix('registrar')->group(function () {
    // Student Registrations (admin view)
    Route::get('registrations', [RegistrationController::class, 'index'])->name('registrations.index');
    Route::patch('registrations/{application}', [RegistrationController::class, 'update'])->name('registrations.update');

    // Module Enrollments
    Route::get('enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::post('enrollments', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::patch('enrollments/{module}', [EnrollmentController::class, 'update'])->name('enrollments.update');
    Route::delete('enrollments/{module}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy');
});