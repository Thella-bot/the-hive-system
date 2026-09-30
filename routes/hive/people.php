<?php

declare(strict_types=1);

use App\Http\Controllers\Hive\Admin\ImportUsersController;
use App\Http\Controllers\Hive\Admin\UserApprovalController;
use App\Http\Controllers\Hive\RolesController;
use App\Http\Controllers\Hive\SettingsController;
use App\Http\Controllers\Hive\StaffController;
use App\Http\Controllers\Hive\StudentController;
use App\Http\Controllers\Hive\UserController;
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
    Route::get('logs', fn () => redirect('/log-viewer'))->name('logs');
});

// System settings. Gated on the seeded `manage-settings` permission rather than
// a role list, so an administrator can narrow access without a code change.
Route::middleware(['permission:manage-settings'])->name('settings.')->group(function () {
    Route::get('settings', [SettingsController::class, 'index'])->name('index');
    Route::patch('settings', [SettingsController::class, 'update'])->name('update');
});

// Role and permission matrix. Gated on the seeded `manage-roles` permission.
Route::middleware(['permission:manage-roles'])->name('roles.')->group(function () {
    Route::get('roles', [RolesController::class, 'index'])->name('index');
    Route::patch('roles/{role}', [RolesController::class, 'update'])->name('update');
});

// Student management (super-admin, admissions-officer, registrar, program-coordinator)
// Registered before the resource route so "export" is not captured as a student id.
Route::get('students/export', [StudentController::class, 'export'])
    ->name('students.export')
    ->middleware('role:super-admin|admissions-officer|registrar|program-coordinator');
Route::resource('students', StudentController::class)
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
