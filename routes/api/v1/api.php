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

// Auth routes
Route::prefix('v1')->group(function () {
    Route::get('users', [UserController::class, 'index']);

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

        // Additional routes for supervisor and C-Level
        Route::group(['prefix'=>'supervisor'], function() {
            Route::get('staff', [UserController::class, 'getStaffOfSupervisor']);
            Route::get('report-status', [ReportController::class, 'getStaffReportStatus']);
            Route::get('/staff/{id}/daily-reports', [ReportController::class, 'getStaffDailyReports']);
            Route::get('/staff/{id}/daily-reports/{year}/{month}/{week}', [ReportController::class, 'filterStaffDailyReports']);
        });

        Route::group(['prefix' => 'c-level'], function () {
            Route::get('supervisor-staff/{divisionId}/list', [UserController::class, 'getCLevelStaff']);
            Route::get('report-status/{divisionId}/check', [ReportController::class, 'getDivisionDailyReports']);
            Route::get('{id}/daily-reports', [ReportController::class, 'getStaffDailyReports']);
            Route::get('{id}/{division}/{year}/{month}/{week}', [ReportController::class, 'filterCLevelStaffDailyReports']);
        });
    });

    // Feedback routes
    Route::prefix('feedback')->middleware('auth:api')->group(function () {
        Route::get('monthly', [FeedbackController::class, 'getUserMonthlyFeedback']);

        Route::group(['prefix'=>'supervisor'], function() {
            Route::get('staff/{id}/{year}/{month}', [FeedbackController::class,'getStaffMonthlyFeedback']);
            Route::post('staff/{id}/{year}/{month}', [FeedbackController::class,'createStaffMonthlyFeedback']);
        });

        Route::group(['prefix' => 'c-level'], function () {
            Route::get('staff/{id}/{year}/{month}', [FeedbackController::class,'getStaffMonthlyFeedback']);
            Route::post('staff/{id}/{year}/{month}', [FeedbackController::class,'createStaffMonthlyFeedback']);
        });
    });

    // KPI routes
    Route::prefix('kpi')->middleware('auth:api')->group(function () {
        Route::get('supervisor/{id}/{month}', [KpiStaffController::class, 'getStaffKpi']);
        Route::post('supervisor/{id}/{month}', [KpiStaffController::class, 'kpiStaffCreate']);
        Route::post('/division/{year}/{month}', [DivisionKPIController::class, 'CreateDivisionKPI']);
        Route::get('/division/{year}/{month}', [DivisionKPIController::class, 'ShowDivisionKPI']);
    });
});
