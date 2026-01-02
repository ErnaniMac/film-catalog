<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\TmdbController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\PasswordResetController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('/tmdb/search', [TmdbController::class, 'search']);

// Registration and email verification
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/email/verify', [RegisterController::class, 'verify']);
Route::post('/email/verification-notification', [RegisterController::class, 'resendVerification']);

// Password reset
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Favorites
    Route::apiResource('favorites', FavoriteController::class)->only(['index', 'store', 'destroy']);
    
    // Admin routes
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('permissions', PermissionController::class);
    });
});

