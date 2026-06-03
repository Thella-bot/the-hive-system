<?php

use App\Http\Controllers\Hive\StaffController;
use App\Http\Controllers\Hive\StudentController;
use App\Http\Controllers\Hive\UserController;
use App\Http\Controllers\Hive\Admin\UserApprovalController;
use App\Http\Controllers\Hive\Admin\ImportUsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| People Management Routes
|--------------------------------------------------------------------------
|
| Routes for managing users, students, and staff members.
|
*/

Route::resource('users', UserController::class)
    ->middleware('role:super-admin|school-admin');

// Admin-only user management
Route::middleware(['role:super-admin|school-admin'])->name('admin.')->prefix('admin')->group(function () {
    Route::get('approve-users', [UserApprovalController::class, 'index'])->name('approve-users');
    Route::post('approve-users/{user}', [UserApprovalController::class, 'approve'])->name('approve-users.approve');
    Route::get('import-users', [ImportUsersController::class, 'show'])->name('import-users');
    Route::post('import-users', [ImportUsersController::class, 'import'])->name('import-users.store');

    // Log Viewer
    Route::get('logs', fn() => redirect('/log-viewer'))->name('logs');
});

// Student and staff resource routes
Route::resource('students', StudentController::class)
    ->middleware('role:super-admin|school-admin');
Route::resource('staff', StaffController::class)
    ->middleware('role:super-admin|school-admin');
