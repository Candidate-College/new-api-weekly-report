<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Division;
use App\Models\DailyReport;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\DailyReportResource;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    protected $userSortingService;

    public function __construct(UserService $userSortingService)
    {
        $this->userSortingService = $userSortingService;
    }

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

    public function checkUserDailyReport()
    {
        $userId = Auth::id();
        $today = now()->startOfDay();

        $dailyReportExists = DailyReport::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->exists();

        return response()->json(['filled_today' => $dailyReportExists]);
    }

    public function getUserDailyReports()
    {
        $userId = Auth::id();
        $reports = DailyReport::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($reports->isEmpty()) {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }

        return DailyReportResource::collection($reports);
    }

    public function getAllDailyReport()
    {
        $user = Auth::user();
        $division = Division::where('id', $user->division_id)->first();
        
        $users = User::where('division_id', $user->division_id)->get();
        $reports = DailyReport::whereIn('user_id', $users->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'division_name' => $division->name,
            'reports' => DailyReportResource::collection($reports),
        ]);
    }

    public function getCLevelDailyReport()
    {
        $user = Auth::user();
        $clevelDivision = CLevelDivision::where('clevel_id', $user->id)->first();
        
        if (!$clevelDivision) {
            return response()->json(['message' => 'C-level division not found'], Response::HTTP_NOT_FOUND);
        }
        
        $users = User::where('division_id', $clevelDivision->division_id)->get();
        $reports = DailyReport::whereIn('user_id', $users->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'division_name' => $clevelDivision->division->name,
            'reports' => DailyReportResource::collection($reports),
        ]);
    }
}
