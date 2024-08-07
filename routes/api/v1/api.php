<?php

use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('users', [UserController::class, 'index']);


    // Route Auth
    Route::group(['prefix' => 'auth'], function ($router) {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::group(['middleware' => 'auth:api'], function($router) {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/user-profile', [AuthController::class, 'userProfile']);
        });
    });

     // Route Report
    Route::prefix('reports')
        ->middleware('auth:api')
        ->group(function () {
            Route::get('weekly', [ReportController::class, 'getWeeklyReport']);
            Route::post('daily', [ReportController::class, 'createDailyReport']);
            Route::get('daily/check', [ReportController::class, 'checkDailyReport']);
            Route::get('staff-daily', [ReportController::class, 'getStaffDailyReport']);
        });

    // Route Feedback
    Route::prefix('feedback')
        ->middleware('auth:api')
        ->group(function () {
            Route::get('monthly', [FeedbackController::class, 'getMonthlyFeedback']);
            Route::post('monthly', [FeedbackController::class, 'createMonthlyFeedback']);
        });
});
