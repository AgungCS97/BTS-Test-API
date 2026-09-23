<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

// Membatasi hit Auth maksimal 3x dalam 60 detik (Sesuai instruksi opsional)
RateLimiter::for('auth_limit', function (Request $request) {
    return Limit::perMinute(3)->by($request->ip());
});

// Membatasi hit Mutasi Product (Estimasi 1x per 5 detik = 12x per menit)
RateLimiter::for('product_limit', function (Request $request) {
    return Limit::perMinute(12)->by($request->ip());
});

// ----------------------------------------------------
// ROUTES AUTHENTICATION
// ----------------------------------------------------
Route::middleware(['throttle:auth_limit'])->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
});

// ----------------------------------------------------
// ROUTES PRODUCTS
// ----------------------------------------------------
// Public Routes (Bisa diakses tanpa login)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Protected Routes (Harus login & dibatasi rate limit)
Route::middleware(['auth:sanctum', 'throttle:product_limit'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});
