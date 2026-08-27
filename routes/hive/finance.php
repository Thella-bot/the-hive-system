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

// Finance routes (super-admin, finance)
Route::middleware(['role:super-admin|finance'])->name('finance.')->prefix('finance')->group(function () {
    // Invoices
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::get('invoices/search-students', [InvoiceController::class, 'searchStudents'])->name('invoices.searchStudents');
    Route::post('invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate')->middleware('throttle:10,1');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store')->middleware('throttle:30,1');
    Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::patch('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update')->middleware('throttle:30,1');
    Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy')->middleware('throttle:30,1');

    // Payments
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store')->middleware('throttle:30,1');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
    Route::get('payments/{payment}/receipt', [PaymentController::class, 'downloadReceipt'])->name('payments.receipt');
    Route::patch('payments/{payment}', [PaymentController::class, 'update'])->name('payments.update')->middleware('throttle:30,1');
    Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy')->middleware('throttle:30,1');
    Route::patch('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify')->middleware('throttle:30,1');

    // Expenses
    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::get('expenses/categories', [ExpenseController::class, 'categories'])->name('expenses.categories');
    Route::post('expenses/categories', [ExpenseController::class, 'storeCategory'])->name('expenses.categories.store')->middleware('throttle:30,1');
    Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store')->middleware('throttle:30,1');
    Route::get('expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
    Route::patch('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update')->middleware('throttle:30,1');
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy')->middleware('throttle:30,1');
    Route::patch('expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve')->middleware('throttle:30,1');
    Route::patch('expenses/{expense}/reject', [ExpenseController::class, 'reject'])->name('expenses.reject')->middleware('throttle:30,1');
    Route::patch('expenses/{expense}/mark-paid', [ExpenseController::class, 'markPaid'])->name('expenses.markPaid')->middleware('throttle:30,1');

    // Expense Categories (nested routes)
    Route::patch('expenses/categories/{category}', [ExpenseController::class, 'updateCategory'])->name('expenses.categories.update')->middleware('throttle:30,1');
    Route::delete('expenses/categories/{category}', [ExpenseController::class, 'destroyCategory'])->name('expenses.categories.destroy')->middleware('throttle:30,1');

    // Budgets
    Route::get('budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::post('budgets', [BudgetController::class, 'store'])->name('budgets.store')->middleware('throttle:30,1');
    Route::get('budgets/{budget}', [BudgetController::class, 'show'])->name('budgets.show');
    Route::patch('budgets/{budget}', [BudgetController::class, 'update'])->name('budgets.update')->middleware('throttle:30,1');
    Route::delete('budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy')->middleware('throttle:30,1');
    Route::patch('budgets/{budget}/activate', [BudgetController::class, 'activate'])->name('budgets.activate')->middleware('throttle:30,1');
    Route::patch('budgets/{budget}/close', [BudgetController::class, 'close'])->name('budgets.close')->middleware('throttle:30,1');

    // Financial Reports
    Route::get('reports/dashboard', [FinancialReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('reports/income', [FinancialReportController::class, 'income'])->name('reports.income');
    Route::get('reports/expenses', [FinancialReportController::class, 'expenses'])->name('reports.expenses');
    Route::get('reports/age-analysis', [FinancialReportController::class, 'ageAnalysis'])->name('reports.ageAnalysis');
    Route::get('reports/student/{user}', [FinancialReportController::class, 'studentLedger'])->name('reports.studentLedger');

    // Convectionary Income
    Route::get('convectionary', [ConvectionaryIncomeController::class, 'index'])->name('convectionary.index');
    Route::get('convectionary/create', [ConvectionaryIncomeController::class, 'create'])->name('convectionary.create');
    Route::post('convectionary', [ConvectionaryIncomeController::class, 'store'])->name('convectionary.store')->middleware('throttle:30,1');
    Route::get('convectionary/{convectionary}', [ConvectionaryIncomeController::class, 'show'])->name('convectionary.show');
    Route::patch('convectionary/{convectionary}', [ConvectionaryIncomeController::class, 'update'])->name('convectionary.update')->middleware('throttle:30,1');
    Route::delete('convectionary/{convectionary}', [ConvectionaryIncomeController::class, 'destroy'])->name('convectionary.destroy')->middleware('throttle:30,1');
});

Route::get('finance/invoices/{invoice}', [InvoiceController::class, 'show'])->name('finance.invoices.show')->middleware('role:super-admin|it-support|finance|registrar|student');
