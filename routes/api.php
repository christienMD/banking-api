<?php

use App\Http\Controllers\API\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// All routes require API key
Route::middleware('api.key')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        
        // Protected auth routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Admin routes
        Route::middleware('can:create,App\Models\User')->group(function () {
            Route::post('users', [AuthController::class, 'storeUser']);
        });
        
        Route::apiResource('customers', App\Http\Controllers\API\CustomerController::class);
        
    });
});