<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider in the app/Providers directory,
| and all routes defined here will be prefixed with the 'api' group.
|
*/

// Public API routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/signup', [AuthController::class, 'signup']);

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::controller(ChatController::class)->group(function () {
        Route::post('/chat/send', 'send');
        Route::post('/chat/clear', 'clear');
        Route::get('/chat/history', 'index'); // Assuming index returns history in JSON
    });
});
