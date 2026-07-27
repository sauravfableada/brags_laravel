<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ForgotPasswordController;
use App\Http\Controllers\API\BrandRegisterController;

Route::prefix('v1')->group(function () {
    // Brand Routes
    Route::post('/brand/register', [BrandRegisterController::class, 'register']);
    Route::post('/brand/login', [AuthController::class, 'login']);

    // Seller Routes
    Route::post('/seller/calculator/sbs', [\App\Http\Controllers\API\SellerCalculatorController::class, 'sbs']);

    // Bragger Routes
    Route::post('/bragger/register', [\App\Http\Controllers\API\BraggerRegisterController::class, 'register']);
    Route::post('/bragger/login', [AuthController::class, 'login']);
    Route::post('/bragger/calculator', [\App\Http\Controllers\API\BraggerCalculatorController::class, 'calculate']);
    // Auth Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login/otp/send', [AuthController::class, 'sendLoginOtp']);
    Route::post('/login/otp/verify', [AuthController::class, 'loginWithOtp']);
    
    // Forgot Password Routes
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp']);
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        
        // Profile Routes
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/change-password', [ProfileController::class, 'changePassword']);
    });
});
