<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CustomerAuthController;

// These routes will have the 'api/customer' prefix and 'customer.' name prefix

Route::post('/login', [CustomerAuthController::class, 'login']);

Route::middleware(['auth:api', 'role:Customer'])->group(function () {
    Route::post('/logout', [CustomerAuthController::class, 'logout']);
    
    Route::get('/dashboard', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'Welcome to the Customer Dashboard',
            'data' => $request->user()
        ]);
    });
});
