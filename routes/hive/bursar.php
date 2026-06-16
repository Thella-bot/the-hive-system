<?php

use App\Http\Controllers\Hive\Bursar\InvoiceController;
use App\Http\Controllers\Hive\Bursar\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Bursar Management Routes
|--------------------------------------------------------------------------
|
| Routes for managing invoices and payments (bursar functionality).
|
*/

// Finance routes (super-admin, finance, hr-manager)
Route::middleware(['role:super-admin|finance|hr-manager'])->name('bursar.')->prefix('bursar')->group(function () {
    // Invoices
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::patch('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

    // Payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::patch('payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    Route::patch('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
});