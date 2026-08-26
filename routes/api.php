<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatMessageController;
use App\Http\Controllers\Api\ChatAttachmentController;
use App\Http\Controllers\Api\ChatReactionController;
use App\Http\Controllers\Api\ChatReadReceiptController;
use App\Http\Controllers\Api\ChatTypingController;
use App\Http\Controllers\Api\TaskController;

// Chat routes - auth:sanctum required
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/modules/{module}/messages', [ChatMessageController::class, 'index'])
        ->middleware(['throttle:60,1'])
        ->name('messages.index');
    Route::post('/modules/{module}/messages', [ChatMessageController::class, 'store'])
        ->middleware(['throttle:30,1'])
        ->name('messages.store');

    Route::get('/channels/{channel}/messages', [ChatMessageController::class, 'indexChannel'])
        ->middleware(['throttle:60,1'])
        ->name('channels.messages.index');
    Route::post('/channels/{channel}/messages', [ChatMessageController::class, 'storeChannel'])
        ->middleware(['throttle:30,1'])
        ->name('channels.messages.store');
    Route::patch('/channels/{channel}/messages/{message}', [ChatMessageController::class, 'update'])
        ->middleware(['throttle:30,1'])
        ->name('channels.messages.update');
    Route::delete('/channels/{channel}/messages/{message}', [ChatMessageController::class, 'destroy'])
        ->middleware(['throttle:30,1'])
        ->name('channels.messages.destroy');

    // Direct message channel creation
    Route::post('/channels/direct', [ChatMessageController::class, 'storeDirectChannel'])
        ->middleware(['throttle:10,1'])
        ->name('channels.direct.store');

    // Message search
    Route::get('/channels/{channel}/search', [ChatMessageController::class, 'search'])
        ->middleware(['throttle:30,1'])
        ->name('channels.messages.search');

    // Read receipts
    Route::post('/channels/{channel}/read', [ChatReadReceiptController::class, 'markAsRead'])
        ->middleware(['throttle:30,1'])
        ->name('channels.messages.markRead');
    Route::get('/channels/{channel}/messages/{message}/reads', [ChatReadReceiptController::class, 'index'])
        ->name('channels.messages.reads');
    Route::get('/channels/{channel}/unread', [ChatReadReceiptController::class, 'unreadCount'])
        ->name('channels.messages.unread');

    // Typing indicators
    Route::post('/channels/{channel}/typing', [ChatTypingController::class, 'invoke'])
        ->middleware(['throttle:10,1'])
        ->name('channels.typing');

    // Message reactions
    Route::get('/channels/{channel}/messages/{message}/reactions', [ChatReactionController::class, 'index'])
        ->name('channels.messages.reactions.index');
    Route::post('/channels/{channel}/messages/{message}/reactions', [ChatReactionController::class, 'store'])
        ->middleware(['throttle:30,1'])
        ->name('channels.messages.reactions.store');
    Route::delete('/channels/{channel}/messages/{message}/reactions', [ChatReactionController::class, 'destroy'])
        ->middleware(['throttle:30,1'])
        ->name('channels.messages.reactions.destroy');

    // File attachments
    Route::post('/chat/attachments', [ChatAttachmentController::class, 'store'])
        ->middleware(['throttle:20,1'])
        ->name('attachments.store');
    Route::get('/chat/attachments/{path}', [ChatAttachmentController::class, 'show'])
        ->name('attachments.download');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Student tasks
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::patch('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
});