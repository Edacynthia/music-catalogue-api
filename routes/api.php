<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TrackController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/tracks', [TrackController::class, 'index']);
    Route::post('/tracks', [TrackController::class, 'store']);
    Route::get('/tracks/{track}', [TrackController::class, 'show']);
    Route::put('/tracks/{track}', [TrackController::class, 'update']);
    Route::delete('/tracks/{track}', [TrackController::class, 'destroy']);
});

