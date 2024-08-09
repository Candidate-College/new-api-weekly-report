<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    
    public function getWeeklyReport(Request $request)
    {
        $user = Auth::guard('api')->user();

        $request->validate([
            'week' => 'required|integer|min:1|max:4',
            'month' => 'required|integer|between:1,12',
        ]);

        $weekNumber = (int) $request->input('week');
        $month = (int) $request->input('month');
        $year = Carbon::now()->year;

        $startDates = [
            1 => Carbon::create($year, $month, 1),
            2 => Carbon::create($year, $month, 8),
            3 => Carbon::create($year, $month, 15),
            4 => Carbon::create($year, $month, 22),
        ];

        $endDates = [
            1 => Carbon::create($year, $month, 7),
            2 => Carbon::create($year, $month, 14),
            3 => Carbon::create($year, $month, 21),
            4 => Carbon::create($year, $month, Carbon::daysInMonth($month, $year)),
        ];

        if (!isset($startDates[$weekNumber])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid week number.',
            ], 400);
        }

        $startDate = $startDates[$weekNumber];
        $endDate = $endDates[$weekNumber];

        $dailyReports = DailyReport::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'week' => $weekNumber,
                'month' => $month,
                'year' => $year,
                'reports' => $dailyReports,
            ],
        ], 200);
    }

    public function createDailyReport(Request $request)
    {
        $currentDay = Carbon::now()->dayOfWeek;

        if ($currentDay < Carbon::MONDAY || $currentDay > Carbon::FRIDAY) {
            return response()->json([
                'success' => false,
                'message' => 'Daily reports can only be submitted on weekdays (Monday to Friday).',
            ], 403);
        }

        $validatedData = $request->validate([
            'content_text' => 'required|string|max:255',
            'content_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::guard('api')->user();

        $dailyReport = DailyReport::create([
            'user_id' => $user->id,
            'created_at' => Carbon::now(),
            'content_text' => $validatedData['content_text'],
            'content_photo' => $this->uploadPhoto($request->file('content_photo')),
            'last_updated_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Daily report created successfully',
            'data' => $dailyReport,
        ], 201);
    }

    /**
     * Handle the photo upload and return the file path
     *
     * @param \Illuminate\Http\UploadedFile|null $photo
     * @return string|null
     */
    protected function uploadPhoto($photo)
    {
        if ($photo) {
            $uploadPath = 'uploads/photos/';
            
            $fileName = time() . '-' . $photo->getClientOriginalName();
            
            $photo->move(public_path($uploadPath), $fileName);

            return $uploadPath . $fileName;
        }

        return null;
    }

    public function checkDailyReport()
    {
        $user = Auth::guard('api')->user();

        $today = Carbon::now()->startOfDay();

        $reportExists = DailyReport::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'report_filled' => $reportExists,
                'message' => $reportExists ? 'Daily report is already filled for today.' : 'No daily report filled for today.',
            ],
        ], 200);
    }

    public function editDailyReport(Request $request)
    {
        $validatedData = $request->validate([
            'content_text' => 'required|string|max:255',
            'content_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::guard('api')->user();

        $today = Carbon::now()->startOfDay();

        $dailyReport = DailyReport::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->first();

        if (!$dailyReport) {
            return response()->json([
                'success' => false,
                'message' => 'No daily report found for today.',
            ], 404);
        }

        $dailyReport->update([
            'content_text' => $validatedData['content_text'],
            'content_photo' => $this->uploadPhoto($request->file('content_photo')) ?? $dailyReport->content_photo,
            'last_updated_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Daily report updated successfully.',
            'data' => $dailyReport,
        ], 200);
    }

    public function deleteDailyReport()
    {
        $user = Auth::guard('api')->user();

        $today = Carbon::now()->startOfDay();

        $dailyReport = DailyReport::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->first();

        if (!$dailyReport) {
            return response()->json([
                'success' => false,
                'message' => 'No daily report found for today.',
            ], 404);
        }

        if ($dailyReport->content_photo) {
            @unlink(public_path($dailyReport->content_photo));
        }

        $dailyReport->delete();

        return response()->json([
            'success' => true,
            'message' => 'Daily report deleted successfully.',
        ], 200);
    }

    public function getStaffDailyReport(Request $request)
    {
        $user = Auth::guard('api')->user();
        
        $division = $user->division;

        $month = $request->query('month', Carbon::now()->month);
        $week = $request->query('week', 1);

        $startDate = Carbon::create()->month($month)->year(Carbon::now()->year)->startOfMonth();
        $endDate = Carbon::create()->month($month)->year(Carbon::now()->year)->endOfMonth();

        switch ($week) {
            case 2:
                $startDate->addDays(7);
                $endDate = $startDate->copy()->endOfWeek();
                break;
            case 3:
                $startDate->addDays(14);
                $endDate = $startDate->copy()->endOfWeek();
                break;
            case 4:
                $startDate->addDays(21);
                $endDate = $startDate->copy()->endOfMonth();
                break;
            default:
                $endDate = $startDate->copy()->addDays(6);
                break;
        }

        $staffReports = DailyReport::whereHas('user', function ($query) use ($division) {
            $query->where('division', $division)
                  ->where('Stflag', true);
        })->whereBetween('created_at', [$startDate, $endDate])
          ->get();

        return response()->json([
            'success' => true,
            'data' => $staffReports,
        ], 200);
    }

    public function getAllDailyReport(Request $request)
{
    $user = Auth::guard('api')->user();

    if (!$user->isCLevel()) {
        return response()->json(['message' => 'Unauthorized access'], 403);
    }

    $validatedData = $request->validate([
        'month' => 'required|integer|min:1|max:12',
        'week'  => 'required|integer|min:1|max:4',
    ]);

    $month = $validatedData['month'];
    $week = $validatedData['week'];
    $year = now()->year;

    $weekRanges = [
        1 => [1, 7],
        2 => [8, 14],
        3 => [15, 21],
        4 => [22, Carbon::create($year, $month)->endOfMonth()->day],
    ];

    [$startDay, $endDay] = $weekRanges[$week];

    $startDate = Carbon::create($year, $month, $startDay)->startOfDay();
    $endDate = Carbon::create($year, $month, $endDay)->endOfDay();

    $divisions = CLevelDivision::where('c_level_id', $user->id)->pluck('division_id');

    $staffReports = DailyReport::whereHas('user', function ($query) use ($divisions) {
        $query->whereIn('division_id', $divisions);
    })->whereBetween('created_at', [$startDate, $endDate])->get();

    return response()->json([
        'success' => true,
        'data' => $staffReports,
    ], 200);
}

}
