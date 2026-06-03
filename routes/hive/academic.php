<?php

use App\Http\Controllers\Hive\ProgrammeSoughtController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Academic Management Routes
|--------------------------------------------------------------------------
|
| Routes for managing programme variants (programme seeks).
|
*/

Route::middleware(['role:super-admin|school-admin'])->name('academic.')->prefix('academic')->group(function () {
    // Programme variants
    Route::resource('programme-seeks', ProgrammeSoughtController::class);
});
