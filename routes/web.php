<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\InstallerController;
use Illuminate\Support\Facades\Route;

// Public root
Route::get('/', function () {
    if (optional(auth())->check() ?? false) {
        return redirect()->route('chat.index');
    }
    return redirect()->route('auth.login');
});

// ── Web GUI Installer Routes ──────────────────────────────────
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallerController::class, 'step1'])->name('index');
    Route::get('/step1', [InstallerController::class, 'step1'])->name('step1');
    Route::get('/step2', [InstallerController::class, 'step2'])->name('step2');
    Route::post('/step2', [InstallerController::class, 'processStep2'])->name('step2.process');
    Route::get('/step3', [InstallerController::class, 'step3'])->name('step3');
    Route::post('/step3', [InstallerController::class, 'processStep3'])->name('step3.process');
    Route::get('/finish', [InstallerController::class, 'finish'])->name('finish');
});

// ── Authentication Routes ────────────────────────────────────
Route::controller(AuthController::class)->group(function () {
    Route::get('/signup', 'showSignup')->name('auth.signup')->middleware('guest');
    Route::post('/signup', 'signup')->name('auth.signup.submit')->middleware('guest');
    Route::get('/login', 'showLogin')->name('auth.login')->middleware('guest');
    Route::post('/login', 'login')->name('auth.login.submit')->middleware('guest');
    Route::post('/logout', 'logout')->name('logout')->middleware('auth');
});

// ── Chat & Conversation Routes (Protected) ───────────────────
Route::controller(ChatController::class)->middleware('auth')->group(function () {
    Route::get('/chat', 'index')->name('chat.index');
    Route::get('/chat/{id}', 'show')->name('chat.show');
    Route::post('/chat/send', 'send')->name('chat.send');
    Route::post('/chat/stream', 'stream')->name('chat.stream');
    Route::post('/chat/clear', 'clear')->name('chat.clear');
    Route::post('/chat/{id}/rename', 'rename')->name('chat.rename');
    Route::delete('/chat/{id}', 'destroy')->name('chat.destroy');
    Route::get('/conversations', 'conversations')->name('conversations.index');
});

// ── Admin Control Panel Routes (Protected by auth & admin) ────
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{id}/toggle-role', [AdminController::class, 'toggleRole'])->name('users.toggle-role');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});
