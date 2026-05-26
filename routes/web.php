<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\CohortController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// --- Public routes ---
Route::get('/', [\App\Http\Controllers\PublicController::class, 'home'])
    ->name('home');
Route::get('/about', [\App\Http\Controllers\PublicController::class, 'about'])
    ->name('about');
Route::get('/programmes', [\App\Http\Controllers\PublicController::class, 'programmes'])
    ->name('programmes');
Route::get('/contact', [\App\Http\Controllers\PublicController::class, 'contact'])
    ->name('contact');
Route::post('/contact', [\App\Http\Controllers\PublicController::class, 'sendContact'])
    ->name('contact.store');

// --- Public auth routes (handled by Jetstream) ---
// Login, password reset, etc. are registered by Jetstream automatically.

// --- Authenticated routes ---
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

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

    // Search
    Route::get('search', [SearchController::class, 'index'])->name('search.index');

    // Calendar
    Route::resource('events', EventController::class)->except(['show', 'create', 'edit']);
});
