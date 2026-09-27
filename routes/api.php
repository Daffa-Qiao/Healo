<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/profile', fn (Request $request) => new UserResource($request->user()));
    });
});
