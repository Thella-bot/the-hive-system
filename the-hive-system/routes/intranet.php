<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Intranet\DashboardController;
use App\Http\Controllers\Intranet\Admin\UserApprovalController;
use App\Http\Controllers\Intranet\Admin\ImportUsersController;

Route::domain('intranet.hbculinaryinstitute.co.ls')
    ->middleware(['auth:sanctum', 'verified'])   // Jetstream uses 'auth:sanctum'
    ->group(function () {
        // All intranet pages
        Route::get('/', [DashboardController::class, 'index'])->name('intranet.dashboard');

        // Admin only
        Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
            Route::get('/approve-users', [UserApprovalController::class, 'index'])->name('approve-users');
            Route::post('/approve-users/{user}', [UserApprovalController::class, 'approve'])->name('approve-user');
            Route::get('/import-users', [ImportUsersController::class, 'show'])->name('import-users');
            Route::post('/import-users', [ImportUsersController::class, 'import'])->name('import-users.post');
        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('intranet.profile.edit');
        Route::post('/profile', [ProfileController::class, 'update'])->name('intranet.profile.update');

        // Leave Requests
        Route::resource('leaves', LeaveRequestController::class)->names('intranet.leaves');
        Route::post('leaves/{leave}', [LeaveRequestController::class, 'update'])->name('intranet.leaves.update');

        // Payslips
        Route::get('/payslips', [PayslipController::class, 'index'])->name('intranet.payslips.index');
        Route::get('/payslips/upload', [PayslipController::class, 'create'])->name('intranet.payslips.create');
        Route::post('/payslips', [PayslipController::class, 'store'])->name('intranet.payslips.store');
        Route::get('/payslips/{payslip}/download', [PayslipController::class, 'download'])->name('intranet.payslips.download');
        });
    });