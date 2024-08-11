<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\KpiStaffController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('users', [UserController::class, 'index']);


    // Route Auth
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::group(['middleware' => 'auth:api'], function() {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/user-profile', [AuthController::class, 'userProfile']);
        });
    });

     // Route Report
    Route::prefix('reports')
        ->middleware('auth:api')
        ->group(function () {

            Route::get('check', [ReportController::class, 'checkUserDailyReport']);
            Route::post('daily', [ReportController::class, 'createUserDailyReports']);
            Route::get('completion', [ReportController::class, 'getUserWeeklyReportCompletion']);
            Route::get('', [ReportController::class, 'getUserDailyReports']);
            Route::get('{year}/{month}/{week}', [ReportController::class, 'filterUserDailyReports']);
            
            // Route Supervisor in Daily Reports
            Route::group(['prefix'=>'supervisor'], function(){
                Route::get('staff', [UserController::class, 'getStaffOfSupervisor']);
                Route::get('report-status', [ReportController::class, 'getStaffReportStatus']);
                Route::get('/staff/{id}/daily-reports', [ReportController::class, 'getStaffDailyReports']);
                Route::get('/staff/{id}/daily-reports/{year}/{month}/{week}', [ReportController::class, 'filterStaffDailyReports']);
            });

            // Route c-level in Daily Reports
            Route::group(['prefix' => 'c-level'], function () {
                Route::get('supervisor-staff/{divisionId}/list', [UserController::class, 'getCLevelStaff']);
                Route::get('report-status/{divisionId}/check', [ReportController::class, 'getDivisionDailyReports']);
                Route::get('{id}/daily-reports', [ReportController::class, 'getStaffDailyReports']);
                Route::get('{id}/{division}/{year}/{month}/{week}', [ReportController::class, 'filterCLevelStaffDailyReports']);
            });

            Route::get('weekly', [ReportController::class, 'getWeeklyReport']);
            Route::get('staff-daily', [ReportController::class, 'getStaffDailyReport']);
        });

    // Route Feedback
    Route::prefix('feedback')
        ->middleware('auth:api')
        ->group(function () {
            Route::get('monthly', [FeedbackController::class, 'getUserMonthlyFeedback']);

            // Route Supervisor in Feedback
            Route::group(['prefix'=>'supervisor'], function(){
                Route::get('staff/{id}/{year}/{month}', [FeedbackController::class,'getStaffMonthlyFeedback']);
                Route::post('staff/{id}/{year}/{month}', [FeedbackController::class,'createStaffMonthlyFeedback']);
            });

            Route::group(['prefix' => 'c-level'], function () {
                Route::get('staff/{id}/{year}/{month}', [FeedbackController::class,'getStaffMonthlyFeedback']);
                Route::post('staff/{id}/{year}/{month}', [FeedbackController::class,'createStaffMonthlyFeedback']);
            });
        });

    Route::prefix('kpi')
        ->middleware('auth:api')
        ->group(function () {
            Route::get('supervisor/{id}/{month}', [KpiStaffController::class, 'show']);
            Route::post('supervisor/{id}/{month}', [KpiStaffController::class, 'kpiStaffCreate']);
        });
});