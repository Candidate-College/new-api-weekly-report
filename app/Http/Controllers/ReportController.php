<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\CLevelDivision;


class ReportController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/reports/weekly",
     *   summary="Get a user's weekly report",
     *   description="Fetches the weekly report for the authenticated user based on the specified week and month.",
     *   tags={"Reports"},
     *   @OA\Parameter(
     *     name="week",
     *     in="query",
     *     description="The week number (1-4) within the month",
     *     required=true,
     *     @OA\Schema(type="integer", minimum=1, maximum=4)
     *   ),
     *   @OA\Parameter(
     *     name="month",
     *     in="query",
     *     description="The month number (1-12)",
     *     required=true,
     *     @OA\Schema(type="integer", minimum=1, maximum=12)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Weekly report retrieved successfully",
     *     @OA\JsonContent(type="object", example={"success": true, "data": {}})
     *   ),
     *   @OA\Response(response=400, description="Invalid week number"),
     *   @OA\Response(response=401, description="Unauthorized"),
     * )
     */
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
            4 => Carbon::create($year, $month, Carbon::create($year, $month)->endOfMonth()->day),
        ];

        if (!isset($startDates[$weekNumber])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid week number.',
            ], 400);
        }

        $startDate = $startDates[$weekNumber];
        $endDate = $endDates[$weekNumber];

        if ($endDate->gt(Carbon::create($year, $month)->endOfMonth())) {
            $endDate = Carbon::create($year, $month)->endOfMonth();
        }

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


    /**
     * @OA\Post(
     *   path="/api/reports/daily",
     *   summary="Create a daily report",
     *   description="Allows the authenticated user to submit a daily report, but only on weekdays (Monday to Friday).",
     *   tags={"Reports"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"content_text"},
     *       @OA\Property(property="content_text", type="string", description="The content of the daily report"),
     *       @OA\Property(property="content_photo", type="file", description="Optional photo for the daily report")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Daily report created successfully",
     *     @OA\JsonContent(type="object", example={"success": true, "data": {}})
     *   ),
     *   @OA\Response(response=403, description="Daily report submission restricted to weekdays"),
     *   @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function createDailyReport(Request $request)
    {
        $currentDay = Carbon::now()->dayOfWeek;

        if ($currentDay < Carbon::MONDAY || $currentDay > Carbon::FRIDAY) {
            return response()->json([
                'success' => false,
                'message' => 'Daily reports can only be submitted on weekdays (Monday to Friday).',
            ], 403);
        }

        $user = Auth::guard('api')->user();

        $today = Carbon::now()->startOfDay();
        $existingReport = DailyReport::where('user_id', $user->id)
                                    ->whereDate('created_at', $today)
                                    ->first();

        if ($existingReport) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted a daily report today.',
            ], 403);
        }

        $validatedData = $request->validate([
            'content_text' => 'required|string|max:255',
            'content_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $dailyReport = DailyReport::create([
            'user_id' => $user->id,
            'created_at' => Carbon::now(),
            'content_text' => $validatedData['content_text'],
            'content_photo' => $this->uploadPhoto($request->file('content_photo')),
            'last_updated_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Daily report created successfully.',
            'data' => $dailyReport,
        ], 201);
    }


    /**
     * Handles photo upload for daily reports.
     * 
     * @param \Illuminate\Http\UploadedFile|null $photo The photo file to be uploaded.
     * @return string|null Returns the file path if the photo is uploaded, or null if no photo is provided.
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

    /**
     * @OA\Get(
     *   path="/api/reports/check",
     *   summary="Check if a daily report is submitted for today",
     *   description="Checks if the authenticated user has already submitted a daily report for the current day.",
     *   tags={"Reports"},
     *   @OA\Response(
     *     response=200,
     *     description="Daily report check successful",
     *     @OA\JsonContent(type="object", example={"success": true, "data": {}})
     *   ),
     *   @OA\Response(response=401, description="Unauthorized"),
     * )
     */
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

    /**
     * @OA\Put(
     *   path="/api/reports/daily",
     *   summary="Edit today's daily report",
     *   description="Allows the authenticated user to edit their daily report for the current day.",
     *   tags={"Reports"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"content_text"},
     *       @OA\Property(property="content_text", type="string", description="Updated content of the daily report"),
     *       @OA\Property(property="content_photo", type="file", description="Optional updated photo for the daily report")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Daily report updated successfully",
     *     @OA\JsonContent(type="object", example={"success": true, "data": {}})
     *   ),
     *   @OA\Response(response=404, description="No daily report found for today"),
     *   @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function editDailyReport(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'content_text' => 'required|string|max:255',
            'content_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::guard('api')->user();

        // Get the current date
        $today = Carbon::now()->startOfDay();

        // Find today's daily report for the user
        $dailyReport = DailyReport::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->first();

        if (!$dailyReport) {
            return response()->json([
                'success' => false,
                'message' => 'No daily report found for today.',
            ], 404);
        }

        // Store the current photo path
        $contentPhoto = $dailyReport->content_photo;

        // Check if a new photo is provided
        if ($request->hasFile('content_photo')) {
            // Delete the old photo if it exists
            if ($contentPhoto && Storage::exists($contentPhoto)) {
                Storage::delete($contentPhoto);
            }

            // Upload the new photo
            $file = $request->file('content_photo');
            $contentPhoto = $file->store('photos', 'public'); // Save the new file and get the path
        } elseif ($request->input('content_photo') === null) {
            // If content_photo is null, delete the old photo if it exists
            if ($contentPhoto && Storage::exists($contentPhoto)) {
                Storage::delete($contentPhoto);
            }

            // Set contentPhoto to null since no new photo is provided
            $contentPhoto = null;
        }

        // Prepare update data
        $updateData = [
            'content_text' => $validatedData['content_text'],
            'content_photo' => $contentPhoto,
            'last_updated_at' => Carbon::now(),
        ];

        // Only add 'content_text' if it's present in the validated data
        if (isset($validatedData['content_text'])) {
            $updateData['content_text'] = $validatedData['content_text'];
        }

        // Debugging: Output the updateData array for inspection
        // dd($updateData);

        // Update the daily report with new data
        try {
            $dailyReport->where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->update($updateData);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Daily report updated successfully.',
            'data' => $updateData,
        ], 200);
    }
     
    /**
     * @OA\Delete(
     *   path="/api/reports/daily",
     *   summary="Delete today's daily report",
     *   description="Allows the authenticated user to delete their daily report for the current day.",
     *   tags={"Reports"},
     *   @OA\Response(
     *     response=200,
     *     description="Daily report deleted successfully",
     *     @OA\JsonContent(type="object", example={"success": true, "message": "Daily report deleted successfully"})
     *   ),
     *   @OA\Response(response=404, description="No daily report found for today"),
     *   @OA\Response(response=401, description="Unauthorized"),
     * )
     */
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

        // Ensure content_photo is a valid path before trying to unlink
        if ($dailyReport->content_photo && file_exists(public_path($dailyReport->content_photo))) {
            @unlink(public_path($dailyReport->content_photo));
        }

        $dailyReport->where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Daily report deleted successfully.',
        ], 200);
    }

    /**
     * @OA\Get(
     *   path="/api/reports/staff",
     *   summary="Get staff's daily reports for a specific week",
     *   description="Fetches the authenticated staff's daily reports within a specified week and month.",
     *   tags={"Reports"},
     *   @OA\Parameter(
     *     name="week",
     *     in="query",
     *     description="The week number (1-5)",
     *     required=true,
     *     @OA\Schema(type="integer", minimum=1, maximum=5)
     *   ),
     *   @OA\Parameter(
     *     name="month",
     *     in="query",
     *     description="The month number (1-12)",
     *     required=true,
     *     @OA\Schema(type="integer", minimum=1, maximum=12)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Staff daily reports retrieved successfully",
     *     @OA\JsonContent(type="object", example={"success": true, "data": {}})
     *   ),
     *   @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function getStaffDailyReport(Request $request)
    {
        $user = Auth::guard('api')->user();

        // Validate query parameters
        $request->validate([
            'week' => 'required|integer|min:1|max:5',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $week = $request->input('week');
        $month = $request->input('month');

        // Determine the start and end dates for the given week and month
        $year = Carbon::now()->year; // Current year or specify if needed
        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->addWeeks($week - 1)->startOfWeek();
        $endDate = $startDate->copy()->endOfWeek();

        // Fetch daily reports within the specified date range
        $dailyReports = DailyReport::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'reports' => $dailyReports,
            ],
        ], 200);
    }    

    /**
     * @OA\Get(
     *   path="/api/reports/all",
     *   summary="Get all staff daily reports for a specific week",
     *   description="Fetches all daily reports for the authenticated user's division within a specified week and month.",
     *   tags={"Reports"},
     *   @OA\Parameter(
     *     name="month",
     *     in="query",
     *     description="The month number (1-12)",
     *     required=true,
     *     @OA\Schema(type="integer", minimum=1, maximum=12)
     *   ),
     *   @OA\Parameter(
     *     name="week",
     *     in="query",
     *     description="The week number (1-4)",
     *     required=true,
     *     @OA\Schema(type="integer", minimum=1, maximum=4)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="All daily reports retrieved successfully",
     *     @OA\JsonContent(type="object", example={"success": true, "data": {}})
     *   ),
     *   @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function getAllDailyReport(Request $request)
    {
        $user = Auth::guard('api')->user();

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
