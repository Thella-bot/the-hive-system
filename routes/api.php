<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatMessageController;
use App\Http\Controllers\Api\TaskController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Student tasks
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::patch('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    // Legacy module-scoped chat
    Route::get('/modules/{module}/messages', [ChatMessageController::class, 'index'])->name('messages.index');
    Route::post('/modules/{module}/messages', [ChatMessageController::class, 'store'])
        ->middleware('throttle:60,1')
        ->name('messages.store');

    // New channel-scoped chat
    Route::get('/channels/{channel}/messages', [ChatMessageController::class, 'indexChannel'])->name('channels.messages.index');
    Route::post('/channels/{channel}/messages', [ChatMessageController::class, 'storeChannel'])
        ->middleware('throttle:60,1')
        ->name('channels.messages.store');
});