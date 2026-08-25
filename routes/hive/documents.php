<?php

use App\Http\Controllers\Hive\DocumentProductionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', config('jetstream.auth_session')])
    ->name('documents.production.')
    ->prefix('documents/production')
    ->group(function () {
        Route::get('/', [DocumentProductionController::class, 'index'])->name('index');

        Route::post('/generate', [DocumentProductionController::class, 'generate'])
            ->middleware('role:super-admin|it-support|academic-director|program-coordinator|admissions-officer|examination-cell|registrar|finance|procurement-manager|storekeeper|hr-manager|librarian|career-services|events-pr-manager|cafeteria-manager')
            ->name('generate');

        Route::get('/audit', [DocumentProductionController::class, 'audit'])->name('audit');
        Route::post('/audit/batch', [DocumentProductionController::class, 'batchGenerate'])
            ->middleware('role:super-admin|it-support')
            ->name('audit.batch');
    });
