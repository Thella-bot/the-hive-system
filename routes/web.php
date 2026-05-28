<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// --- Public routes ---
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('hive.dashboard');
    }
    return app(PublicController::class)->home();
})->name('home');

Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/programmes', [PublicController::class, 'programmes'])->name('programmes');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::get('/apply', [PublicController::class, 'apply'])->name('apply');
Route::post('/apply', [ApplicationController::class, 'store'])->name('public.apply.store');
Route::post('/contact', [PublicController::class, 'sendContact'])->name('contact.store');

// --- Public auth routes (handled by Jetstream) ---
Route::get('/login', fn () => Inertia::render('Auth/Login'))->name('login');
Route::get('/forgot-password', fn () => Inertia::render('Auth/ForgotPassword'))->name('password.request');
Route::get('/reset-password/{token}', fn (string $token) => Inertia::render('Auth/ResetPassword', [
    'token' => $token,
    'email' => request('email'),
]))->name('password.reset');
