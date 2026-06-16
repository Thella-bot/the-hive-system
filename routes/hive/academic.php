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

// Academic routes (super-admin, academic-director, program-coordinator)
Route::middleware(['role:super-admin|academic-director|program-coordinator'])->name('academic.')->prefix('academic')->group(function () {
    // Programme variants
    Route::resource('programme-seeks', ProgrammeSoughtController::class)->only(['index', 'show', 'update', 'destroy']);
});
