<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DailyReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\DailyReportResource;

class ReportController extends Controller
{
    public function getStaffOfSupervisor()
    {
        $supervisorId = Auth::id();
        $staff = User::where('supervisor_id', $supervisorId)
            ->select('id', 'profile_picture', 'first_name', 'last_name')
            ->get();

        return UserResource::collection($staff);
    }

    public function getWeeklyReport(Request $request)
    {
        // Logic to fetch and filter weekly report
    }

    public function createDailyReport(Request $request)
    {
        // Logic to create daily report
    }

    public function checkDailyReport()
    {
        // Logic to check if daily report is filled
    }

    public function getStaffDailyReports($id)
    {
        $reports = DailyReport::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return DailyReportResource::collection($reports);
    }

    public function filterDailyReports($id, $year, $month, $week)
    {
        $startDate = new \DateTime("first day of $year-$month");
        $startDate->modify('+' . (($week - 1) * 7) . ' days');
        $endDate = clone $startDate;
        $endDate->modify('+6 days');

        $reports = DailyReport::where('user_id', $id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        return DailyReportResource::collection($reports);
    }

    public function getCLevelStaff()
    {
        $cLevelId = Auth::id();

        // Get supervisors under the C-Level
        $supervisors = User::where('supervisor_id', $cLevelId)
            ->select('id', 'profile_picture', 'first_name', 'last_name')
            ->with(['staff' => function($query) {
                $query->select('id', 'profile_picture', 'first_name', 'last_name', 'supervisor_id');
            }])
            ->get();

        return response()->json([
            'c_level_id' => $cLevelId,
            'supervisors' => $supervisors->map(function ($supervisor) {
                return [
                    'supervisor' => new UserResource($supervisor),
                    'staff' => UserResource::collection($supervisor->staff)
                    
                ];
            })
        ]);
    }

}
