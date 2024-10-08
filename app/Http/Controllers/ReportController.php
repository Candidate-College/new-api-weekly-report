<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Division;
use App\Models\DailyReport;
use App\Models\CLevelDivision;
use DateTime;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\DailyReportResource;
use Illuminate\Support\Facades\Storage;


class ReportController extends Controller
{
    protected UserService $userSortingService;

    public function __construct(UserService $userSortingService)
    {
        $this->userSortingService = $userSortingService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/check",
     *     summary="Cek apakah User sudah mengisi daily report",
     *     description="Menandai bahwa user sudah mengisi laporan hari ini atau belum",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Status pengisian daily report hari ini.",
     *         @OA\JsonContent(
     *             @OA\Property(property="filled_today", type="boolean", example=true)
     *         )
     *     )
     * )
     */

    public function checkUserDailyReport()
    {
        $userId = Auth::id();
        $today = now()->startOfDay();
        $dailyReportExists = DailyReport::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->exists();

        return response()->json(['filled_today' => $dailyReportExists]);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/reports/weekly",
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

        if (!empty($user->id)) {
            $dailyReports = DailyReport::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        if (!empty($dailyReports)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'week' => $weekNumber,
                    'month' => $month,
                    'year' => $year,
                    'reports' => $dailyReports,
                ],
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No daily reports found for the specified week.',
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/v1/reports/daily",
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
        if (!empty($user->id)) {
            $existingReport = DailyReport::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->first();
        }

        if (!empty($existingReport)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted a daily report today.',
            ], 403);
        }

        $validatedData = $request->validate([
            'content_text' => 'required|string|max:255',
            'content_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if (!empty($user->id)) {
            $dailyReport = DailyReport::create([
                'user_id' => $user->id,
                'created_at' => Carbon::now(),
                'content_text' => $validatedData['content_text'],
                'content_photo' => $this->uploadPhoto($request->file('content_photo')),
                'last_updated_at' => Carbon::now(),
            ]);
        }

        if (!empty($dailyReport)) {
            return response()->json([
                'success' => true,
                'message' => 'Daily report created successfully.',
                'data' => $dailyReport,
            ], 201);
        }
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
     *     path="/api/v1/reports",
     *     summary="Menampilkan semua daily report dari user",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daily report berhasil ditemukan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="user_id", type="integer", example=123),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-14T12:34:56Z"),
     *                 @OA\Property(property="content_text", type="string", example="Contoh teks laporan harian."),
     *                 @OA\Property(property="content_photo", type="string", format="uri", example="https://example.com/photo.jpg"),
     *                 @OA\Property(property="last_updated_at", type="string", format="date-time", example="2024-08-14T12:34:56Z")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Tidak Data Daily Report.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error.")
     *         )
     *     )
     * )
     */

    public function getUserDailyReports()
    {
        $userId = Auth::id();
        $reports = DailyReport::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
        if ($reports->isEmpty()) {
            return response()->json(['message' => 'Data not found'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return DailyReportResource::collection($reports);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/{year}/{month}/{week}",
     *     summary="Menampilkan daily report dari user yang sudah difilter",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="2024"),
     *         description="Tahun laporan"
     *     ),
     *     @OA\Parameter(
     *         name="month",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=8),
     *         description="Bulan laporan"
     *     ),
     *     @OA\Parameter(
     *         name="week",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=3),
     *         description="Minggu laporan"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daily report berhasil ditemukan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-10T08:00:00Z"),
     *                 @OA\Property(property="content_text", type="string", example="Laporan harian lengkap."),
     *                 @OA\Property(property="content_photo", type="string", format="uri", example="https://example.com/photo.jpg"),
     *                 @OA\Property(property="last_updated_at", type="string", format="date-time", example="2024-08-10T09:00:00Z")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data tidak ditemukan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Data not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Terjadi kesalahan pada server.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error.")
     *         )
     *     )
     * )
     */

    public function filterUserDailyReports($year, $month, $week)
    {
        $userId = Auth::id();
        $startDate = new DateTime("$year-$month-01");

        $startDate->modify('+' . (($week - 1) * 7) . ' days');

        $endDate = clone $startDate;
        $endDate->modify('+6 days');

        $lastDayOfMonth = new DateTime("last day of $year-$month");
        if ($endDate > $lastDayOfMonth) {
            $endDate = $lastDayOfMonth;
        }

        $reports = DailyReport::where('user_id', $userId)
            ->whereBetween('created_at', [
                $startDate->format('Y-m-d 00:00:00'),
                $endDate->format('Y-m-d 23:59:59')
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($reports->isEmpty()) {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }

        return DailyReportResource::collection($reports);
    }

    public function createUserDailyReports(Request $request)
    {
        $userId = Auth::id();

        $validatedData = $request->validate([
            'content_text' => 'required|string',
            'content_photo' => 'nullable|image|max:2048',
            'report_date' => 'nullable|date',
        ]);

        $reportDate = $validatedData['report_date'] ?? now()->toDateString();

        if (Carbon::parse($reportDate)->isAfter(now())) {
            return response()->json([
                'message' => 'You cannot create a report for a future date.',
            ], 400);
        }

        if (Carbon::parse($reportDate)->isWeekend()) {
            return response()->json([
                'message' => 'You cannot create a report on Saturday or Sunday.',
            ], 400);
        }

        $existingReport = DailyReport::where('user_id', $userId)
            ->whereDate('created_at', $reportDate)
            ->first();

        if ($existingReport) {
            return response()->json([
                'message' => 'Daily report for this date already exists.',
            ], 400);
        }

        $dailyReport = new DailyReport([
            'user_id' => $userId,
            'content_text' => $validatedData['content_text'],
            'content_photo' => $validatedData['content_photo'] ?? null,
            'created_at' => $reportDate,
            'last_updated_at' => now(),
        ]);

        $dailyReport->save();

        return new DailyReportResource($dailyReport);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/completion",
     *     summary="Melihat berapa persen user sudah mengisi report pada week ini",
     *     description="Menampilkan berapa persen user sudah mengisi daily report pada week ini",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mendapatkan persentase penyelesaian laporan mingguan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="weekly_report_completion_percentage", type="integer", example=75)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Token tidak valid atau terjadi kesalahan server internal.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error. Token tidak valid.")
     *         )
     *     )
     * )
     */
    public function getUserWeeklyReportCompletion(Request $request)
    {
        $userId = Auth::id();
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $dailyReportCount = DailyReport::where('user_id', $userId)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();

        $workDays = 5;
        $completionPercentage = ($dailyReportCount / $workDays) * 100;

        $completionPercentage = min($completionPercentage, 100);

        return response()->json([
            'weekly_report_completion_percentage' => round($completionPercentage, 2)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/supervisor/report-status",
     *     summary="Supervisor mengecek apakah staff-staffnya sudah mengisi daily report",
     *     description="Menampilkan semua staffnya apakah sudah mengisi daily report atau belum",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil mendapatkan status laporan harian staff.",
     *         @OA\JsonContent(
     *             @OA\Property(property="date", type="string", example="14 Aug 2024"),
     *             @OA\Property(property="staff_report_status", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=17),
     *                 @OA\Property(property="name", type="string", example="Ona Rau"),
     *                 @OA\Property(property="profile_picture", type="string", nullable=true, example="https://via.placeholder.com/640x480.png/00aa00?text=eos"),
     *                 @OA\Property(property="report_submitted", type="boolean", example=true)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Token tidak valid atau terjadi kesalahan internal server.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error. Token tidak valid.")
     *         )
     *     )
     * )
     */

    public function getStaffReportStatus(Request $request)
    {
        $supervisorId = Auth::id();
        $staffMembers = User::where('supervisor_id', $supervisorId)
            ->orWhere('vice_supervisor_id', $supervisorId)
            ->select('id', 'first_name', 'last_name', 'profile_picture')
            ->get();

        $today = now()->startOfDay();

        $staffReportStatus = $staffMembers->map(function ($staff) use ($today) {
            $reportSubmitted = DailyReport::where('user_id', $staff->id)
                ->whereDate('created_at', $today)
                ->exists();

            return [
                'id' => $staff->id,
                'name' => "{$staff->first_name} {$staff->last_name}",
                'profile_picture' => $staff->profile_picture,
                'report_submitted' => $reportSubmitted
            ];
        });

        return response()->json([
            'date' => now()->format('j M Y'),
            'staff_report_status' => $staffReportStatus
        ]);
    }

    /**
     * @OA\Put(
     *   path="/api/v1/reports/daily",
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
        if (!empty($user->id)) {
            $dailyReport = DailyReport::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->first();
        }

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
     *   path="/api/v1/reports/daily",
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

        if (!empty($user->id)) {
            $dailyReport = DailyReport::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->first();
        }

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

        if (!empty($user->id)) {
            $dailyReport->where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->delete();
        }

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

        // Calculate the start date of the specified week
        $startDate = Carbon::create($year, $month, 1)
            ->startOfMonth()
            ->addWeeks($week - 1)
            ->startOfWeek();

        // Calculate the end date of the specified week
        $endDate = $startDate->copy()->endOfWeek();

        // Fetch daily reports within the specified date range
        if (!empty($user->id)) {
            $dailyReports = DailyReport::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->orderBy('created_at', 'desc')
                ->get();
        }

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
     *     path="/api/v1/reports/supervisor/staff-daily/{id}/{year}/{month}/{week}",
     *     summary="Supervisor mengecek weekly report seorang staff",
     *     description="Menampilkan list daily report staff dalam seminggu (week)",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=17),
     *         description="ID staff yang akan diperiksa"
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=2024),
     *         description="Tahun laporan"
     *     ),
     *     @OA\Parameter(
     *         name="month",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=8),
     *         description="Bulan laporan"
     *     ),
     *     @OA\Parameter(
     *         name="week",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=33),
     *         description="Minggu laporan"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daily reports berhasil diambil.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="user_id", type="integer", example=17),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-14 00:00:00"),
     *                 @OA\Property(property="content_text", type="string", example="Completed tasks for today."),
     *                 @OA\Property(property="content_photo", type="string", example="link example.jpg"),
     *                 @OA\Property(property="last_updated_at", type="string", format="date-time", example="2024-08-14 06:33:56")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error atau token tidak valid.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error atau token tidak valid.")
     *         )
     *     )
     * )
     */

    public function filterStaffDailyReports($id, $year, $month, $week)
    {
        $startDate = new DateTime("first day of $year-$month");
        $startDate->modify('+' . (($week - 1) * 7) . ' days');
        $endDate = clone $startDate;
        $endDate->modify('+6 days');

        $reports = DailyReport::where('user_id', $id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($reports->isEmpty()) {
            return response()->json(['message' => 'Data not found'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return DailyReportResource::collection($reports);
    }

    /**
     * @OA\Get(
     *   path="/api/v1/reports/all",
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

    public function getAllDailyReport()
    {
        $user = Auth::user();
        if (!empty($user->division_id)) {
            $division = Division::where('id', $user->division_id)->first();
        }
        if (!empty($user->division_id)) {
            $users = User::where('division_id', $user->division_id)->get();
        }
        $reports = DailyReport::whereIn('user_id', $users->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'division_name' => $division->name,
            'reports' => DailyReportResource::collection($reports),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/c-level/report-status/{divisionId}/check",
     *     summary="C-Level melihat apakah staff dan supervisor perdivisi sudah mengisi daily report",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="divisionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="ID divisi yang ingin diperiksa"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status laporan berhasil diambil.",
     *         @OA\JsonContent(
     *             @OA\Property(property="division_id", type="integer", example=1),
     *             @OA\Property(property="division_name", type="string", example="Web Development"),
     *             @OA\Property(property="report_date", type="string", format="date", example="2024-08-14"),
     *             @OA\Property(property="team_members", type="array", @OA\Items(
     *                 @OA\Property(property="name", type="string", example="Elsie Lebsack"),
     *                 @OA\Property(property="role", type="string", example="Head"),
     *                 @OA\Property(property="profile_picture", type="string", example="https://via.placeholder.com/640x480.png/0099ff?text=eum"),
     *                 @OA\Property(property="report_filled_today", type="boolean", example=false)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error, token tidak valid.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error, token tidak valid.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized, token tidak valid.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */

    public function getDivisionDailyReports(Request $request, $divisionId)
    {
        $cLevel = Auth::user();
        if (isset($cLevel->CFlag) && !$cLevel->CFlag) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $division = Division::findOrFail($divisionId);
        $today = Carbon::today()->format('j M Y');

        $users = User::where('division_id', $divisionId)->get();

        if (!empty($cLevel->id)) {
            $sortedUsers = $this->userSortingService->sortUsersforClevel($users, $cLevel->id);
        }

        $result = [
            'division_id' => $division->id,
            'division_name' => $division->name,
            'report_date' => $today,
            'team_members' => $sortedUsers->map(function ($user) {
                return [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'profile_picture' => $user['profile_picture'],
                    'report_filled_today' => $this->hasFilledReportToday($user['id'])
                ];
            })
        ];

        return response()->json($result);
    }

    private function hasFilledReportToday($userId)
    {
        return DailyReport::where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/c-level/{id}/{division}/{year}/{month}/{week}",
     *     summary="C-Level melihat daily report dari seorang staff atau supervisor per divisi",
     *     tags={"Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="ID C-Level"
     *     ),
     *     @OA\Parameter(
     *         name="division",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="Marketing"),
     *         description="Divisi"
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=2024),
     *         description="Tahun laporan"
     *     ),
     *     @OA\Parameter(
     *         name="month",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=8),
     *         description="Bulan laporan"
     *     ),
     *     @OA\Parameter(
     *         name="week",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=2),
     *         description="Minggu laporan"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Laporan berhasil ditemukan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="user_id", type="integer", example=17),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-14T00:00:00"),
     *                 @OA\Property(property="content_text", type="string", example="Completed tasks for today."),
     *                 @OA\Property(property="content_photo", type="string", nullable=true, example=null),
     *                 @OA\Property(property="last_updated_at", type="string", format="date-time", example="2024-08-14T06:33:56")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Token tidak valid atau tidak berwenang.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */

    public function filterCLevelStaffDailyReports($id, $division, $year, $month, $week)
    {
        $startDate = new DateTime("first day of $year-$month");
        $startDate->modify('+' . (($week - 1) * 7) . ' days');
        $endDate = clone $startDate;
        $endDate->modify('+6 days');

        $reports = DailyReport::where('user_id', $id)
            ->whereHas('user', function ($query) use ($division) {
                $query->where('division_id', $division);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($reports->isEmpty()) {
            return response()->json(['message' => 'Data not found'], Response::HTTP_NOT_FOUND);
        }

        return DailyReportResource::collection($reports);
    }

    public function getCLevelDailyReport()
    {
        $user = Auth::user();
        if (!empty($user->id)) {
            $clevelDivision = CLevelDivision::where('clevel_id', $user->id)->first();
        }

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
