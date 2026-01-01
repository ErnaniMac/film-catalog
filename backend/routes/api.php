<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\TmdbController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;

// Public routes
Route::get('/tmdb/search', [TmdbController::class, 'search']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Favorites
    Route::apiResource('favorites', FavoriteController::class)->only(['index', 'store', 'destroy']);
    
    // Admin routes
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('permissions', PermissionController::class);
    });
});

