<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AdminAuthController;
use App\Http\Controllers\API\SettingController;

// These routes will have the 'api/admin' prefix and 'admin.' name prefix

Route::post('/login', [AdminAuthController::class, 'login']);

Route::middleware(['auth:api', 'role:Admin'])->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout']);
    
    Route::get('/dashboard', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'Welcome to the Admin Dashboard',
            'data' => $request->user()
        ]);
    });

    // Brand Management
    Route::post('/brands', [\App\Http\Controllers\API\BrandRegisterController::class, 'register']);

    // Category Management
    Route::apiResource('categories', \App\Http\Controllers\API\Admin\CategoryController::class);

    // Settings Routes
    Route::get('/settings/smtp', [SettingController::class, 'getSmtp']);
    Route::post('/settings/smtp', [SettingController::class, 'updateSmtp']);
    
    Route::get('/settings/twilio', [SettingController::class, 'getTwilio']);
    Route::post('/settings/twilio', [SettingController::class, 'updateTwilio']);
    
    Route::get('/settings/payment', [SettingController::class, 'getPayment']);
    Route::post('/settings/payment', [SettingController::class, 'updatePayment']);
});
