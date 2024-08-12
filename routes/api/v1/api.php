<?php

use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\AllowSupervisor;
use App\Http\Middleware\AllowStaff;
use App\Http\Middleware\AllowCLevel;
use App\Http\Middleware\AuthCheck;

// Public route
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Auth routes
Route::prefix('v1')->group(function () {
    Route::get('users', [UserController::class, 'index']);

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:api')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/user-profile', [AuthController::class, 'userProfile']);
        });
    });

    // Report routes
    Route::prefix('reports')->middleware('authCheck')->group(function () {
        Route::get('weekly', [ReportController::class, 'getWeeklyReport'])
            ->middleware('allowSupervisorAndStaff');
        Route::post('daily', [ReportController::class, 'createDailyReport'])
            ->middleware('allowSupervisorAndStaff');
        Route::delete('daily', [ReportController::class, 'deleteDailyReport'])
            ->middleware('allowSupervisorAndStaff');
        Route::put('daily', [ReportController::class, 'editDailyReport'])
            ->middleware('allowSupervisorAndStaff');
        Route::get('daily/check', [ReportController::class, 'checkDailyReport'])
            ->middleware('allowSupervisorAndStaff');
        Route::get('staff-daily', [ReportController::class, 'getStaffDailyReport'])
            ->middleware('allowSupervisor');
        Route::get('all-daily', [ReportController::class, 'getAllDailyReport'])
            ->middleware('allowCLevel');
    });

    // Feedback routes
    Route::prefix('feedback')->group(function () {
        Route::get('monthly', [FeedbackController::class, 'getMonthlyFeedback']);
        Route::post('monthly', [FeedbackController::class, 'createMonthlyFeedback']);
    });
});