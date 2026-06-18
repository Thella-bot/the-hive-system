<?php

use App\Http\Controllers\Hive\LibraryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| eLibrary Routes
|--------------------------------------------------------------------------
|
| Routes for library management: books, loans, reservations, and categories.
|
*/

// Library routes - accessible by authenticated users
Route::middleware(['auth'])->name('library.')->prefix('library')->group(function () {
    // Dashboard
    Route::get('/', [LibraryController::class, 'dashboard'])->name('dashboard');

    // Books
    Route::get('books', [LibraryController::class, 'booksIndex'])->name('books.index');
    Route::get('books/create', [LibraryController::class, 'booksCreate'])->name('books.create');
    Route::post('books', [LibraryController::class, 'booksStore'])->name('books.store');
    Route::get('books/{book}', [LibraryController::class, 'booksShow'])->name('books.show');
    Route::get('books/{book}/edit', [LibraryController::class, 'booksEdit'])->name('books.edit');
    Route::patch('books/{book}', [LibraryController::class, 'booksUpdate'])->name('books.update');
    Route::delete('books/{book}', [LibraryController::class, 'booksDestroy'])->name('books.destroy');

    // Categories (admin/super-admin only)
    Route::middleware(['role:super-admin|finance'])->group(function () {
        Route::get('categories', [LibraryController::class, 'categoriesIndex'])->name('categories.index');
        Route::post('categories', [LibraryController::class, 'categoriesStore'])->name('categories.store');
        Route::patch('categories/{category}', [LibraryController::class, 'categoriesUpdate'])->name('categories.update');
        Route::delete('categories/{category}', [LibraryController::class, 'categoriesDestroy'])->name('categories.destroy');
    });

    // Loans (admin/super-admin)
    Route::middleware(['role:super-admin|finance'])->group(function () {
        Route::get('loans', [LibraryController::class, 'loansIndex'])->name('loans.index');
        Route::post('loans', [LibraryController::class, 'loansStore'])->name('loans.store');
        Route::patch('loans/{loan}/return', [LibraryController::class, 'loansReturn'])->name('loans.return');
        Route::patch('loans/{loan}/renew', [LibraryController::class, 'loansRenew'])->name('loans.renew');
        Route::delete('loans/{loan}', [LibraryController::class, 'loansDestroy'])->name('loans.destroy');
    });

    // Reservations
    Route::get('reservations', [LibraryController::class, 'reservationsIndex'])->name('reservations.index');
    Route::post('reservations', [LibraryController::class, 'reservationsStore'])->name('reservations.store');

    // Admin can fulfill/cancel reservations
    Route::middleware(['role:super-admin|finance'])->group(function () {
        Route::patch('reservations/{reservation}/fulfill', [LibraryController::class, 'reservationsFulfill'])->name('reservations.fulfill');
        Route::patch('reservations/{reservation}/cancel', [LibraryController::class, 'reservationsCancel'])->name('reservations.cancel');
    });
});