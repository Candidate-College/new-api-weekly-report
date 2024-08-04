<?php

use App\Http\Controllers\AuthController;

use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('users', [UserController::class, 'index']);

    // Route Auth
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, , 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    // Route Report
    Route::prefix('reports')->middleware('auth:api')->group(function () {
        Route::get('weekly', [ReportController::class, 'getWeeklyReport']);
        Route::post('daily', [ReportController::class, 'createDailyReport']);
        Route::get('daily/check', [ReportController::class, 'checkDailyReport']);
        Route::get('staff-daily', [ReportController::class, 'getStaffDailyReport']);
    });

    // Route Feedback
    Route::prefix('feedback')->middleware('auth:api')->group(function () {
        Route::get('monthly', [FeedbackController::class, 'getMonthlyFeedback']);
        Route::post('monthly', [FeedbackController::class, 'createMonthlyFeedback']);
    });
});
