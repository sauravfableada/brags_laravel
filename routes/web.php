<?php

use App\Http\Controllers\ApiDocsController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api-docs', [ApiDocsController::class, 'index'])->name('api.docs');

Route::get('/docs', function () {
    return redirect()->route('api.docs');
});

