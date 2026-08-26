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

// User management (super-admin, it-support, hr-manager)
Route::resource('users', UserController::class)
    ->middleware('role:super-admin|it-support|hr-manager');

// Admin-only user management
Route::middleware(['role:super-admin|it-support'])->name('admin.')->prefix('admin')->group(function () {
    Route::get('approve-users', [UserApprovalController::class, 'index'])->name('approve-users');
    Route::post('approve-users/{user}', [UserApprovalController::class, 'approve'])->name('approve-users.approve');
    Route::get('import-users', [ImportUsersController::class, 'show'])->name('import-users');
    Route::post('import-users', [ImportUsersController::class, 'import'])->name('import-users.store');

    // Log Viewer
    Route::get('logs', fn() => redirect('/log-viewer'))->name('logs');
});

// Student management (super-admin, admissions-officer, registrar, program-coordinator)
Route::resource('students', StudentController::class)
    ->middleware('role:super-admin|admissions-officer|registrar|program-coordinator');
Route::get('students/export', [StudentController::class, 'export'])
    ->name('students.export')
    ->middleware('role:super-admin|admissions-officer|registrar|program-coordinator');
Route::get('students/{student}/generate-proof', [StudentController::class, 'generateProof'])
    ->name('students.generate-proof')
    ->middleware('role:super-admin|admissions-officer|registrar|program-coordinator');
Route::get('students/{student}/generate-certificate', [StudentController::class, 'generateCertificate'])
    ->name('students.generate-certificate')
    ->middleware('role:super-admin|admissions-officer|registrar|program-coordinator');
Route::get('students/{student}/generate-reference', [StudentController::class, 'generateReference'])
    ->name('students.generate-reference')
    ->middleware('role:super-admin|admissions-officer|registrar|program-coordinator');

// Staff management (super-admin, hr-manager)
Route::resource('staff', StaffController::class)
    ->middleware('role:super-admin|hr-manager');
Route::get('staff/{staff}/generate-appointment', [StaffController::class, 'generateAppointment'])
    ->name('staff.generate-appointment')
    ->middleware('role:super-admin|hr-manager');
Route::get('staff/{staff}/generate-warning', [StaffController::class, 'generateWarning'])
    ->name('staff.generate-warning')
    ->middleware('role:super-admin|hr-manager');
