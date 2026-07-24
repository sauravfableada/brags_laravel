<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SellerAuthController;

// These routes will have the 'api/seller' prefix and 'seller.' name prefix

Route::post('/login', [SellerAuthController::class, 'login']);

Route::middleware(['auth:api', 'role:Seller'])->group(function () {
    Route::post('/logout', [SellerAuthController::class, 'logout']);
    
    Route::get('/dashboard', function (Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'Welcome to the Seller Dashboard',
            'data' => $request->user()
        ]);
    });
});
