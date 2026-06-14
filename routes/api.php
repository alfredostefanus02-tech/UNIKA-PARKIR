<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes — Parkir Cerdas Unika (Minimal)
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', function () {
    return response()->json([
        'success'   => true,
        'message'   => 'Parkir Cerdas Unika API berjalan ✅',
        'timestamp' => now()->toDateTimeString(),
    ]);
});

// Autentikasi
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/tamu',     [AuthController::class, 'tamuLogin']);
});

// Route dengan token
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profil',  [AuthController::class, 'profil']);
});