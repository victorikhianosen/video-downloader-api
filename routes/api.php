<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogController;
use App\Http\Controllers\YoutubeDownloaderController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/download', [YoutubeDownloaderController::class, 'download']);

Route::post('/generate', [YoutubeDownloaderController::class, 'getAvailableQualities']);

Route::post('/download-resolution', [YoutubeDownloaderController::class, 'downloadWithResolution']);

Route::get('/logs', [LogController::class, 'fetchLogs']);
