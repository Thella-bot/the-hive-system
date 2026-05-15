<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\CohortController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// --- Public auth routes (handled by Jetstream) ---
// Login, password reset, etc. are registered by Jetstream automatically.

// --- Authenticated routes ---
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/', DashboardController::class)->name('dashboard');

    // School Structure
    Route::resource('departments', DepartmentController::class)
        ->middleware('can:view-departments');

    Route::resource('academic-years', AcademicYearController::class)
        ->except('show')
        ->middleware('can:view-academic-years');

    Route::resource('cohorts', CohortController::class)
        ->middleware('can:view-cohorts');

    // People
    Route::resource('users', UserController::class)
        ->middleware('can:view-users');
});
