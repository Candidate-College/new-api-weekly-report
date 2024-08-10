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

class ReportController extends Controller
{
    protected $userSortingService;

    public function __construct(UserService $userSortingService)
    {
        $this->userSortingService = $userSortingService;
    }

    public function getWeeklyReport(Request $request)
    {
        // Logic to fetch and filter weekly report
    }

    public function createDailyReport(Request $request)
    {
        // Logic to create daily report
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
            return response()->json(['message' => 'Data not found'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    return DailyReportResource::collection($reports);
    }

    public function filterUserDailyReports( $year, $month, $week)
    {
        $userId = Auth::id();
        $startDate = new \DateTime("first day of $year-$month");
        $startDate->modify('+' . (($week - 1) * 7) . ' days');
        $endDate = clone $startDate;
        $endDate->modify('+6 days');

        $reports = DailyReport::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($reports->isEmpty()) {
            return response()->json(['message' => 'Data not found'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        return DailyReportResource::collection($reports);
    }
    
    public function createUserDailyReports(Request $request)
{
    $userId = Auth::id();

    // Validasi input untuk tanggal yang akan diisi, default ke hari ini.
    $validatedData = $request->validate([
        'content_text' => 'required|string',
        'content_photo' => 'nullable|image|max:2048',
        'report_date' => 'nullable|date', // Optional, tanggal untuk laporan
    ]);

    // Setel tanggal laporan ke hari ini jika tidak ada yang diberikan.
    $reportDate = $validatedData['report_date'] ?? now()->toDateString();

    // Pastikan tanggal laporan tidak melebihi hari ini
    if (Carbon::parse($reportDate)->isAfter(now())) {
        return response()->json([
            'message' => 'You cannot create a report for a future date.',
        ], 400);
    }

    // Cek apakah laporan harian sudah ada untuk tanggal tersebut.
    $existingReport = DailyReport::where('user_id', $userId)
                                  ->whereDate('created_at', $reportDate)
                                  ->first();

    if ($existingReport) {
        return response()->json([
            'message' => 'Daily report for this date already exists.',
        ], 400);
    }

    // Simpan laporan harian baru
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
    
    public function getStaffDailyReports($id)
    { 
        $reports = DailyReport::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return DailyReportResource::collection($reports);
    }

    public function getStaffReportStatus(Request $request)
    {
    $supervisorId = Auth::id();

    $staffMembers = User::where('supervisor_id', $supervisorId)
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

    public function filterStaffDailyReports($id, $year, $month, $week)
    {
        $startDate = new \DateTime("first day of $year-$month");
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

    public function getDivisionDailyReports(Request $request, $divisionId)
    {

       $cLevel = auth()->user();
       if (!$cLevel->CFlag) {
           return response()->json(['error' => 'Unauthorized'], 403);
       }

       $division = Division::findOrFail($divisionId);
       $today = Carbon::today()->format('j M Y');

       $users = User::where('division_id', $divisionId)->get();

       $sortedUsers = $this->userSortingService->sortUsersforClevel($users, $cLevel->id);

       $result = [
           'division_id' => $division->id,
           'division_name' => $division->name,
           'report_date' => $today,
           'team_members' => $sortedUsers->map(function ($user) use ($today) {
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

   public function filterCLevelStaffDailyReports($id, $division, $year, $month, $week)
   {
       $startDate = new \DateTime("first day of $year-$month");
       $startDate->modify('+' . (($week - 1) * 7) . ' days');
       $endDate = clone $startDate;
       $endDate->modify('+6 days');
   
       // Query to filter reports by user ID, division, and date range
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

}
