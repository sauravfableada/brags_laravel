<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AdminAuthController;

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
});
