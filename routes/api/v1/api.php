<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CLevelDivisionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\KpiStaffController;
use App\Http\Controllers\DivisionKPIController;
use App\Http\Controllers\TestingController;
use App\Http\Middleware\AllowSupervisor;
use App\Http\Middleware\AllowStaff;
use App\Http\Middleware\AllowCLevel;
use App\Http\Middleware\AuthCheck;

// Public route
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Version 1 API routes
Route::prefix('v1')->group(function () {
    // User routes
    Route::get('users', [UserController::class, 'index']);
    Route::get('supervisor/staff', [UserController::class, 'getStaffOfSupervisor'])->middleware('allowSupervisor');
    Route::get('c-level/supervisor-staff/{divisionId}/list', [UserController::class, 'getCLevelStaff'])->middleware('allowCLevel');
    Route::get('division/staff-count', [UserController::class, 'getDivisionAndStaffCount'])->middleware('allowCLevel');

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
    Route::prefix('reports')->middleware('authCheck')->group(function () {

        // Supervisor And Staff report routes
        Route::middleware('allowSupervisorAndStaff')->group(function () {
            Route::post('daily', [ReportController::class, 'createDailyReport']);
            Route::delete('daily', [ReportController::class, 'deleteDailyReport']);
            Route::put('daily', [ReportController::class, 'editDailyReport']);

            Route::get('all', [ReportController::class, 'getAllDailyReports']);
            Route::get('weekly', [ReportController::class, 'getWeeklyReports']);

            Route::get('check', [ReportController::class, 'checkUserDailyReport']);
            Route::get('completion', [ReportController::class, 'getUserWeeklyReportCompletion']);
            Route::get('', [ReportController::class, 'getUserDailyReports']);
            Route::get('{year}/{month}/{week}', [ReportController::class, 'filterUserDailyReports']);
        });

        // Supervisor-specific report routes
        Route::prefix('supervisor')->middleware('allowSupervisor')->group(function () {
            Route::get('staff-daily', [ReportController::class, 'getStaffDailyReport']);

            Route::get('report-status', [ReportController::class, 'getStaffReportStatus']);
            Route::get('staff/{id}/daily-reports', [ReportController::class, 'getStaffDailyReports']);
            Route::get('staff-daily/{id}/{year}/{month}/{week}', [ReportController::class, 'filterStaffDailyReports']);
        });

        // C-Level specific report routes
        Route::prefix('c-level')->middleware('allowCLevel')->group(function () {
            Route::get('report-status/{divisionId}/check', [ReportController::class, 'getDivisionDailyReports']);
            Route::get('{id}/daily-reports', [ReportController::class, 'getStaffDailyReports']);
            Route::get('{id}/{division}/{year}/{month}/{week}', [ReportController::class, 'filterCLevelStaffDailyReports']);
        });
    });

    // Feedback routes
    Route::prefix('feedback')->middleware('authCheck')->group(function () {

        // Supervisor and staff feedback routes
        Route::middleware('allowSupervisorAndStaff')->group(function () {
            Route::get('monthly', [FeedbackController::class, 'getUserMonthlyFeedback']);
            Route::get('staff-performance/{month}', [FeedbackController::class, 'getUserPerformanceFeedback']);
        });

        // Supervisor-specific feedback routes
        Route::middleware('allowSupervisor')->group(function () {
            Route::get('supervisor-staff/{id}/{year}/{month}', [FeedbackController::class, 'getStaffMonthlyFeedback']);
            Route::post('supervisor-staff/{id}/{year}/{month}', [FeedbackController::class, 'createStaffMonthlyFeedback']);
        });

        // C-Level-specific feedback routes
        Route::middleware('allowCLevel')->group(function () {
            Route::post('clevel-supervisor/{id}/{divisionId}/{year}/{month}', [FeedbackController::class, 'createSupervisorMonthlyFeedback']);
            Route::get('clevel-supervisor/{id}/{divisionId}/{year}/{month}', [FeedbackController::class, 'getSupervisorMonthlyFeedback']);
        });
    });

    // KPI routes
    Route::prefix('kpi')->middleware('authCheck')->group(function () {

        // Supervisor and staff KPI routes
        Route::middleware('allowSupervisor')->group(function () {
            Route::get('supervisor-staff/{id}/{month}/score', [KpiStaffController::class, 'getStaffKpi']);
            Route::post('supervisor-staff/{id}/{month}/score', [KpiStaffController::class, 'kpiStaffCreate']);
            Route::post('supervisor-division/{year}/{month}', [DivisionKPIController::class, 'createDivisionKPI']);
            Route::get('supervisor-division/{year}/{month}', [DivisionKPIController::class, 'showDivisionKPI']);
        });

        // C-Level-specific KPI routes
        Route::middleware('allowCLevel')->group(function () {
            Route::post('clevel/{divisionId}/{year}/{month}/score', [DivisionKPIController::class, 'updateScoreDivisionKPI']);
            Route::get('clevel/{divisionId}/{year}/{month}/score', [DivisionKPIController::class, 'showScoreDivisionKPI']);
        });
    });

    // C-level division relationship routes
    Route::post('c-level-division', [CLevelDivisionController::class, 'createDivisionCLevel']);
    Route::get('c-level-division/{divisionId}', [CLevelDivisionController::class, 'showDivisionCLevel']);
    Route::put('c-level-division/{divisionId}', [CLevelDivisionController::class, 'updateDivisionCLevel']);
    Route::delete('c-level-division/{ClevelId}/{divisionId}', [CLevelDivisionController::class, 'deleteDivisionCLevel']);
});

Route::get('/test', [TestingController::class, 'index']);
