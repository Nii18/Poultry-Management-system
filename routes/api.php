<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SensorController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Sensor ingestion endpoint (uses API key authentication)
Route::post('/sensor/ingest', [SensorController::class, 'ingest']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // ==================== AUTHENTICATION ====================
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    

    
    // ==================== SENSORS ====================
    Route::get('/sensors/house/{houseId}', [SensorController::class, 'getLatestReadings']);
    Route::get('/sensors/{sensorId}/history', [SensorController::class, 'getHistory']);
    Route::post('/sensors/register', [SensorController::class, 'registerSensor']);
    Route::put('/sensors/{sensorId}/status', [SensorController::class, 'updateStatus']);
});