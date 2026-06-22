<?php

use App\Http\Controllers\Api\MobileController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function () {
    Route::post('/login', [MobileController::class, 'login']);
    Route::get('/me', [MobileController::class, 'me']);
    Route::post('/practice', [MobileController::class, 'practice']);
    Route::post('/logout', [MobileController::class, 'logout']);
});
