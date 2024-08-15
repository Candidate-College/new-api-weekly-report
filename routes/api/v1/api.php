<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\KpiStaffController;
use App\Http\Controllers\DivisionKPIController;
use App\Http\Middleware\AllowSupervisor;
use App\Http\Middleware\AllowStaff;
use App\Http\Middleware\AllowCLevel;
use App\Http\Middleware\AuthCheck;

// Public route
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Version 1 API routes
Route::prefix('v1')->group(function () {
    // User routes
    Route::get('users', [UserController::class, 'index']);
    Route::get('supervisor/staff', [UserController::class, 'getStaffOfSupervisor']);
    Route::get('c-level/supervisor-staff/{divisionId}/list', [UserController::class, 'getCLevelStaff']);
    Route::get('division/staff-count', [UserController::class, 'getDivisionAndStaffCount']);

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/send-otp', [AuthController::class, 'sendOtp']);
        Route::post('/verify-otp/{token}', [AuthController::class, 'verifyOtp']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password/{token}', [AuthController::class, 'resetPassword']);

        Route::middleware('auth:api')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/user-profile', [AuthController::class, 'userProfile']);
        });
    });

    // Report routes
    Route::prefix('reports')->middleware('auth:api')->group(function () {
        // General report routes
        Route::middleware('authCheck')->group(function () {
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

            // User-specific report routes
            Route::get('check', [ReportController::class, 'checkUserDailyReport']);
            Route::post('daily', [ReportController::class, 'createUserDailyReports']);
            Route::get('completion', [ReportController::class, 'getUserWeeklyReportCompletion']);
            Route::get('', [ReportController::class, 'getUserDailyReports']);
            Route::get('{year}/{month}/{week}', [ReportController::class, 'filterUserDailyReports']);
        });

        // Supervisor-specific routes
        Route::prefix('supervisor')->middleware('allowSupervisor')->group(function () {
            Route::get('report-status', [ReportController::class, 'getStaffReportStatus']);
            Route::get('staff/{id}/daily-reports', [ReportController::class, 'getStaffDailyReports']);
            Route::get('staff-daily/{id}/{year}/{month}/{week}', [ReportController::class, 'filterStaffDailyReports']);
        });

        // C-Level specific routes
        Route::prefix('c-level')->middleware('allowCLevel')->group(function () {
            Route::get('report-status/{divisionId}/check', [ReportController::class, 'getDivisionDailyReports']);
            Route::get('{id}/daily-reports', [ReportController::class, 'getStaffDailyReports']);
            Route::get('{id}/{division}/{year}/{month}/{week}', [ReportController::class, 'filterCLevelStaffDailyReports']);
        });
    });

    // Feedback routes
    Route::prefix('feedback')->middleware('auth:api')->group(function () {
        Route::get('monthly', [FeedbackController::class, 'getUserMonthlyFeedback']);
        Route::get('staff-performance/{month}', [FeedbackController::class, 'getUserPerformanceFeedback']);
        Route::get('supervisor-staff/{id}/{year}/{month}', [FeedbackController::class, 'getStaffMonthlyFeedback']);
        Route::post('supervisor-staff/{id}/{year}/{month}', [FeedbackController::class, 'createStaffMonthlyFeedback']);
        Route::post('clevel-supervisor/{id}/{divisionId}/{year}/{month}', [FeedbackController::class, 'createSupervisorMonthlyFeedback']);
        Route::get('clevel-supervisor/{id}/{divisionId}/{year}/{month}', [FeedbackController::class, 'getSupervisorMonthlyFeedback']);
    });

    // KPI routes
    Route::prefix('kpi')->middleware('auth:api')->group(function () {
        Route::get('supervisor-staff/{id}/{month}/score', [KpiStaffController::class, 'getStaffKpi']);
        Route::post('supervisor-staff/{id}/{month}/score', [KpiStaffController::class, 'kpiStaffCreate']);
        Route::post('supervisor-division/{year}/{month}', [DivisionKPIController::class, 'createDivisionKPI']);
        Route::get('supervisor-division/{year}/{month}', [DivisionKPIController::class, 'showDivisionKPI']);
        Route::post('clevel/{divisionId}/{year}/{month}/score', [DivisionKPIController::class, 'updateScoreDivisionKPI']);
        Route::get('clevel/{divisionId}/{year}/{month}/score', [DivisionKPIController::class, 'showScoreDivisionKPI']);
    });
});
