<?php

namespace App\Http\Controllers;

use App\Models\KPIRating;
use Illuminate\Http\Request;
use App\Models\MonthlyFeedback;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\KpiStaffResource;
use App\Http\Resources\PerformanceFeedbackResource;

class FeedbackController extends Controller
{
            /**
     * @OA\Get(
     *     path="/api/v1/feedback/monthly",
     *     summary="Menampilkan semua performance feedback pengguna",
     *     tags={"Feedback"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Feedback berhasil ditemukan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="year", type="string", example="2024"),
     *                 @OA\Property(property="month", type="integer", example=7),
     *                 @OA\Property(property="content", type="string", example="Kinerja Anda bulan ini sangat baik.")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data tidak ditemukan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Data not found.")
     *         )
     *     )
     * )
     */

    public function getUserMonthlyFeedback()
    {
        $user = Auth::user();
        $feedbacks = MonthlyFeedback::where('user_id', $user->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    
        return PerformanceFeedbackResource::collection($feedbacks);
    }

        /**
     * @OA\Post(
     *     path="/api/v1/feedback/clevel-supervisor/staff/{id}/{year}/{month}",
     *     summary="Supervisor membuat monthly feedback ke seorang staff",
     *     tags={"Feedback"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="ID staff yang akan diberikan feedback"
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="2024"),
     *         description="Tahun feedback"
     *     ),
     *     @OA\Parameter(
     *         name="month",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=8),
     *         description="Bulan feedback"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="content_text", type="string", example="Excellent work on the project!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Feedback berhasil dibuat.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="year", type="string", example="2024"),
     *                 @OA\Property(property="month", type="integer", example=8),
     *                 @OA\Property(property="content", type="string", example="Excellent work on the project!")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Feedback untuk bulan ini sudah ada.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Feedback for this month already exists")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Token tidak valid atau kesalahan server lainnya.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */

    public function createStaffMonthlyFeedback(Request $request, $id, $year, $month)
    {
        $request->validate([
            'content_text' => 'required|string',
        ]);

        $monthlyFeedback = MonthlyFeedback::firstOrCreate(
            ['user_id' => $id, 'year' => $year, 'month' => $month],
            ['content_text' => $request->input('content_text')]
        );

        if (!$monthlyFeedback->wasRecentlyCreated) {
            return response()->json(['message' => 'Feedback for this month already exists'], 409);
        }

        return new PerformanceFeedbackResource($monthlyFeedback);
    }

        /**
     * @OA\Get(
     *     path="/api/v1/feedback/clevel-supervisor/staff/{id}/{year}/{month}",
     *     summary="Supervisor melihat monthly feedback seorang staff",
     *     tags={"Feedback"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=123),
     *         description="ID staff yang ingin dilihat feedback-nya"
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="2024"),
     *         description="Tahun untuk feedback"
     *     ),
     *     @OA\Parameter(
     *         name="month",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=8),
     *         description="Bulan untuk feedback"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Feedback berhasil ditemukan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="year", type="string", example="2024"),
     *                 @OA\Property(property="month", type="integer", example=8),
     *                 @OA\Property(property="content", type="string", example="Excellent work on the project!")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tidak ada feedback ditemukan untuk bulan yang ditentukan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No feedback found for the specified month")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Token tidak valid atau kesalahan server.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token tidak valid atau kesalahan server.")
     *         )
     *     )
     * )
     */

    public function getStaffMonthlyFeedback($id, $year, $month)
    {
        $monthlyFeedback = MonthlyFeedback::where('user_id', $id)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if (!$monthlyFeedback) {
            return response()->json(['message' => 'No feedback found for the specified month'], 404);
        }

        return new PerformanceFeedbackResource($monthlyFeedback);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/feedback/staff-performance/{month}",
     *     summary="Menampilkan performance grade dari user pada bulan yang ditentukan",
     *     tags={"Feedback"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="month",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=7
     *         ),
     *         description="Menentukan bulan yang dipilih"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data performance berhasil ditemukan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object", example={
     *                 "user_id": 123,
     *                 "year": "2024",
     *                 "month": 7,
     *                 "kpi_data": {
     *                     "aspect": "Activeness",
     *                     "total_aspect": 20,
     *                     "value_conversion": "A",
     *                     "type": {
     *                         "kpi": "Q1",
     *                         "end_of_month_realization": 5,
     *                         "score": 100,
     *                         "final_score": 10
     *                     }
     *                 },
     *                 "total_aspects": 100,
     *                 "value_conversion": "Excellent"
     *             }),
     *             @OA\Property(property="data.user_id", type="integer", example=123),
     *             @OA\Property(property="data.year", type="string", example="2024"),
     *             @OA\Property(property="data.month", type="integer", example=7),
     *             @OA\Property(property="data.kpi_data", type="array", @OA\Items(
     *                 @OA\Property(property="aspect", type="string", example="Activeness"),
     *                 @OA\Property(property="total_aspect", type="float", example=20),
     *                 @OA\Property(property="value_conversion", type="string", example="A"),
     *                 @OA\Property(property="type", type="array", @OA\Items(
     *                     @OA\Property(property="kpi", type="string", example="Q1"),
     *                     @OA\Property(property="end_of_month_realization", type="float", example=5),
     *                     @OA\Property(property="score", type="float", example=100),
     *                     @OA\Property(property="final_score", type="float", example=10)
     *                 ))
     *             )),
     *             @OA\Property(property="data.total_aspects", type="float", example=100),
     *             @OA\Property(property="data.value_conversion", type="string", example="Excellent")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data tidak ditemukan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Data Not Found")
     *         )
     *     )
     * )
     */

    public function getUserPerformanceFeedback($month)
    {
        $userId = auth()->id();
        $year = date('Y');
        $kpi = KPIRating::where('user_id', $userId)->where('year', $year)->where('month', $month)->first();
        if (!$kpi) {
            return response()->json(['message' => 'Data not found.'], 404);
        }
        
        return new KpiStaffResource($kpi);
    }

}
