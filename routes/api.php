<?php

use App\Http\Controllers\API\AccountController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\TransactionController;
use Illuminate\Support\Facades\Route;

// All routes require API key
Route::middleware('api.key')->group(function () {
    // Auth routes - public (only require API key)
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
    });
    
    // Protected routes - require authentication
    Route::middleware('auth:sanctum')->group(function () {
        // Auth routes that require authentication
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });
        
        // Admin routes - only for admin users
        Route::middleware('can:create,App\Models\User')->group(function () {
            Route::post('users', [AuthController::class, 'storeUser']);
        });
        
        // Customer resources
        Route::get('customers', [CustomerController::class, 'index']);
        Route::post('customers', [CustomerController::class, 'store']);
        Route::get('customers/{id}', [CustomerController::class, 'show']);
        Route::patch('customers/{id}', [CustomerController::class, 'update']);
        Route::delete('customers/{id}', [CustomerController::class, 'destroy']);
        
       // Account resources
        Route::get('customers/{customerId}/accounts', [AccountController::class, 'index']);
        Route::post('customers/{customerId}/accounts', [AccountController::class, 'store']);
        Route::get('accounts/{accountId}', [AccountController::class, 'show']);
        Route::get('accounts/{accountId}/balance', [AccountController::class, 'getBalance']);

        // Transaction routes
        Route::post('transactions', [TransactionController::class, 'store']);
        Route::get('accounts/{accountId}/transactions', [TransactionController::class, 'index']);
    });
});