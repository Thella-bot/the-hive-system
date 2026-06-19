<?php

use App\Http\Controllers\Hive\Finance\ExpenseController;
use App\Http\Controllers\Hive\Finance\BudgetController;
use App\Http\Controllers\Hive\Finance\InvoiceController;
use App\Http\Controllers\Hive\Finance\PaymentController;
use App\Http\Controllers\Hive\Finance\FinancialReportController;
use App\Http\Controllers\Hive\Finance\ConvectionaryIncomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Finance Management Routes
|--------------------------------------------------------------------------
|
| Routes for managing invoices, payments, expenses, and budgets (finance functionality).
|
*/

// Finance routes (super-admin, finance, hr-manager)
Route::middleware(['role:super-admin|finance|hr-manager'])->name('finance.')->prefix('finance')->group(function () {
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

    // Expenses
    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::get('expenses/categories', [ExpenseController::class, 'categories'])->name('expenses.categories');
    Route::post('expenses/categories', [ExpenseController::class, 'storeCategory'])->name('expenses.categories.store');
    Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
    Route::patch('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::patch('expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');
    Route::patch('expenses/{expense}/reject', [ExpenseController::class, 'reject'])->name('expenses.reject');
    Route::patch('expenses/{expense}/mark-paid', [ExpenseController::class, 'markPaid'])->name('expenses.markPaid');

    // Expense Categories (nested routes)
    Route::patch('expenses/categories/{category}', [ExpenseController::class, 'updateCategory'])->name('expenses.categories.update');
    Route::delete('expenses/categories/{category}', [ExpenseController::class, 'destroyCategory'])->name('expenses.categories.destroy');

    // Budgets
    Route::get('budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::post('budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::get('budgets/{budget}', [BudgetController::class, 'show'])->name('budgets.show');
    Route::patch('budgets/{budget}', [BudgetController::class, 'update'])->name('budgets.update');
    Route::delete('budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');
    Route::patch('budgets/{budget}/activate', [BudgetController::class, 'activate'])->name('budgets.activate');
    Route::patch('budgets/{budget}/close', [BudgetController::class, 'close'])->name('budgets.close');

    // Financial Reports
    Route::get('reports/dashboard', [FinancialReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('reports/income', [FinancialReportController::class, 'income'])->name('reports.income');
    Route::get('reports/expenses', [FinancialReportController::class, 'expenses'])->name('reports.expenses');
    Route::get('reports/age-analysis', [FinancialReportController::class, 'ageAnalysis'])->name('reports.ageAnalysis');
    Route::get('reports/student/{user}', [FinancialReportController::class, 'studentLedger'])->name('reports.studentLedger');

    // Convectionary Income
    Route::get('convectionary', [ConvectionaryIncomeController::class, 'index'])->name('convectionary.index');
    Route::get('convectionary/create', [ConvectionaryIncomeController::class, 'create'])->name('convectionary.create');
    Route::post('convectionary', [ConvectionaryIncomeController::class, 'store'])->name('convectionary.store');
    Route::get('convectionary/{convectionary}', [ConvectionaryIncomeController::class, 'show'])->name('convectionary.show');
    Route::patch('convectionary/{convectionary}', [ConvectionaryIncomeController::class, 'update'])->name('convectionary.update');
    Route::delete('convectionary/{convectionary}', [ConvectionaryIncomeController::class, 'destroy'])->name('convectionary.destroy');
});