<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('users', [\App\Http\Controllers\UserController::class, 'index']);

    // Route Auth
    Route::prefix('auth')->group(function () {
        // 
    });

    // Route Report
    Route::prefix('reports')->middleware('auth:api')->group(function () {
        // 
    });

    // Route Feedback
    Route::prefix('feedback')->middleware('auth:api')->group(function () {
        // 
    });

});
