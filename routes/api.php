<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KbSimulationController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'apiMobileLogin']);
});

Route::post('/calculateTenorMax', [KbSimulationController::class, 'calculateTenorMax']);
//update
Route::prefix('mobile/kb-simulasi')->group(function () {
    Route::get('/config', [KbSimulationController::class, 'mobileConfig']);
    Route::post('/tenor-max', [KbSimulationController::class, 'calculateTenorMax']);
    Route::post('/plafond-max', [KbSimulationController::class, 'calculatePlafondMax']);
    Route::post('/preview', [KbSimulationController::class, 'previewSimulation']);
    Route::post('/calculate', [KbSimulationController::class, 'calculate']);
    Route::post('/store', [KbSimulationController::class, 'store']);
    Route::post('/download-pdf', [KbSimulationController::class, 'downloadPdf']);
});
