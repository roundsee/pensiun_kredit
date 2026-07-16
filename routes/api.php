<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KbSimulationController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'apiMobileLogin']);
});
//update
Route::prefix('mobile/kb-simulasi')->group(function () {
    Route::get('/config', [KbSimulationController::class, 'mobileConfig']);
    Route::post('/calculate', [KbSimulationController::class, 'calculate']);
    Route::post('/store', [KbSimulationController::class, 'store']);
    Route::post('/download-pdf', [KbSimulationController::class, 'downloadPdf']);
});
