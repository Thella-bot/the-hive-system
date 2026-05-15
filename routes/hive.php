<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Hive\UserController;
use App\Http\Controllers\Hive\ProfileController;
use App\Http\Controllers\Hive\PayslipController;
use App\Http\Controllers\Hive\DashboardController;
use App\Http\Controllers\Hive\LeaveRequestController;
use App\Http\Controllers\Hive\TranscriptController;

/*
|--------------------------------------------------------------------------
| Hive Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->name('hive.')
    ->prefix('hive')
    ->group(function () {
        // All Hive pages
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Admin only
        Route::middleware(['role:admin'])->name('admin.')->prefix('admin')->group(function () {
            Route::get('approve-users', [UserController::class, 'approve_users'])->name('approve-users');
        });

        // Profile
        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');

        // Leave Requests
        Route::resource('leave-requests', LeaveRequestController::class)->middleware('can:view-leave-requests');

        // Payslips
        Route::get('payslips', [PayslipController::class, 'index'])->name('payslips.index')->middleware('can:view-payslips');
        Route::get('payslips/{payslip}', [PayslipController::class, 'show'])->name('payslips.show')->middleware('can:view-payslips');

        // Transcript
        Route::get('transcript', [TranscriptController::class, 'index'])->name('transcript.index')->middleware('can:view-transcript');
    });