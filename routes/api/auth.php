<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;

Route::prefix('auth')->group(function () {

    // 1. Send OTP (for both login and registration) - NO CHANGE
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/login-password', [AuthController::class, 'loginWithPassword']);

    // 2. NEW: Verify OTP. This handles BOTH login and the first step of registration.
    // It replaces the old /login-with-otp route.
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

    // 3. NEW: Complete registration. This requires a 'temp_token' from /verify-otp.
    // It replaces the old /register-with-otp route.
    Route::post('/complete-registration', [AuthController::class, 'completeRegistration']);

    // --- EXISTING PROTECTED ROUTES ---
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});
