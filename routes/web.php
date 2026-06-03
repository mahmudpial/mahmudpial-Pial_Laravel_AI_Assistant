<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('chat.index');
    }
    return redirect()->route('auth.login');
});

// Authentication routes
Route::controller(AuthController::class)->group(function () {
    Route::get('/signup', 'showSignup')->name('auth.signup')->middleware('guest');
    Route::post('/signup', 'signup')->name('auth.signup.submit')->middleware('guest');
    Route::get('/login', 'showLogin')->name('auth.login')->middleware('guest');
    Route::post('/login', 'login')->name('auth.login.submit')->middleware('guest');
    Route::post('/logout', 'logout')->name('auth.logout')->middleware('auth');
});

// Chat routes (protected)
Route::controller(ChatController::class)->middleware('auth')->group(function () {
    Route::get('/chat', 'index')->name('chat.index');
    Route::post('/chat/send', 'send')->name('chat.send');
    Route::post('/chat/clear', 'clear')->name('chat.clear');
});

