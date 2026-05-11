<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Hive\DashboardController;
use App\Http\Controllers\Hive\Admin\UserApprovalController;
use App\Http\Controllers\Hive\Admin\ImportUsersController;
use App\Http\Controllers\Hive\LeaveRequestController;
use App\Http\Controllers\Hive\PayslipController;
use App\Http\Controllers\Hive\ProfileController;
use App\Http\Controllers\Hive\TranscriptController;


Route::domain('hive.hbci.ac.ls')
    ->middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->group(function () {
        // All Hive pages
        Route::get('/', [DashboardController::class, 'index'])->name('hive.dashboard');

        // Admin only
        Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
            Route::get('/approve-users', [UserApprovalController::class, 'index'])->name('approve-users');
            Route::post('/approve-users/{user}', [UserApprovalController::class, 'approve'])->name('approve-user');
            Route::get('/import-users', [ImportUsersController::class, 'show'])->name('import-users');
            Route::post('/import-users', [ImportUsersController::class, 'import'])->name('import-users.post');
        });

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('hive.profile.edit');
        Route::post('/profile', [ProfileController::class, 'update'])->name('hive.profile.update');

        // Leave Requests
        Route::resource('leaves', LeaveRequestController::class)
            ->only(['index', 'create', 'store'])
            ->names('intranet.leaves');
        Route::post('leaves/{leave}', [LeaveRequestController::class, 'update'])->name('hive.leaves.update');

        // Payslips
        Route::get('/payslips', [PayslipController::class, 'index'])->name('hive.payslips.index');
        Route::get('/payslips/upload', [PayslipController::class, 'create'])->name('hive.payslips.create');
        Route::post('/payslips', [PayslipController::class, 'store'])->name('hive.payslips.store');
        Route::get('/payslips/{payslip}/download', [PayslipController::class, 'download'])->name('hive.payslips.download');

        Route::get('/transcript/{user}', [TranscriptController::class, 'show'])->name('hive.transcript');
    });
