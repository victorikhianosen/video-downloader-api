<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YoutubeDownloader;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/download', [YoutubeDownloader::class, 'download']);

Route::post('/generate-video', [YoutubeDownloader::class, 'generateVideo']);
