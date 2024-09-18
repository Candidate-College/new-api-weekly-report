<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\KPIRating;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Resources\KpiStaffResource;
use Illuminate\Support\Facades\Validator;

class KpiStaffController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/v1/kpi/supervisor-staff/{id}/{month}/score",
     *     summary="Supervisor menilai KPI seorang staff",
     *     description="Supervisor menilai KPI Rating seorang staff pada bulan yang ditentukan",
     *     tags={"KPI"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=17),
     *         description="ID staff yang dinilai"
     *     ),
     *     @OA\Parameter(
     *         name="month",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=9),
     *         description="Bulan penilaian KPI"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="activeness_Q1_realization", type="number", format="float", example=5.0),
     *             @OA\Property(property="activeness_Q2_realization", type="number", format="float", example=1.0),
     *             @OA\Property(property="activeness_Q3_realization", type="number", format="float", example=2.0),
     *             @OA\Property(property="ability_Q1_realization", type="number", format="float", example=100.0),
     *             @OA\Property(property="communication_Q1_realization", type="number", format="float", example=4.0),
     *             @OA\Property(property="communication_Q2_realization", type="number", format="float", example=4.0),
     *             @OA\Property(property="discipline_Q1_realization", type="number", format="float", example=100.0),
     *             @OA\Property(property="discipline_Q2_realization", type="number", format="float", example=100.0),
     *             @OA\Property(property="discipline_Q3_realization", type="number", format="float", example=100.0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="KPI berhasil dinilai.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="string", example="17"),
     *                 @OA\Property(property="year", type="string", example="2024"),
     *                 @OA\Property(property="month", type="string", example="9"),
     *                 @OA\Property(property="kpi_data", type="array", @OA\Items(
     *                     @OA\Property(property="aspect", type="string", example="activeness"),
     *                     @OA\Property(property="total_aspect", type="integer", example=20),
     *                     @OA\Property(property="value_conversion_aspect", type="string", example="A"),
     *                     @OA\Property(property="type", type="array", @OA\Items(
     *                         @OA\Property(property="kpi", type="string", example="Q1"),
     *                         @OA\Property(property="end_of_month_realization", type="number", format="float", example=5.0),
     *                         @OA\Property(property="score", type="integer", example=100),
     *                         @OA\Property(property="final_score", type="integer", example=10)
     *                     )),
     *                     @OA\Property(property="total_aspects", type="integer", example=100),
     *                     @OA\Property(property="value_conversion", type="string", example="Excellent")
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="KPI untuk pengguna, tahun, dan bulan ini sudah ada.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="KPI for this user, year, and month already exists.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Tidak diizinkan. Staff member ini tidak berada di bawah supervisi Anda.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized. This staff member is not under your supervision.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Token tidak valid.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */

    public function kpiStaffCreate(Request $request, $id, $month)
    {
        $userId = auth()->id();
        $year = date('Y');

        $staff = User::find($id);
        if (!$staff || ($staff->supervisor_id != $userId && $staff->vice_supervisor_id != $userId)) {
            return response()->json(['message' => 'Unauthorized. This staff member is not under your supervision.'], 403);
        }
        $year = date('Y');
        $validator = Validator::make($request->all() + ['user_id' => $id, 'month' => $month], [
            'user_id' => 'required|exists:users,id',
            'month' => 'required|integer|min:1|max:12',
            'activeness_Q1_realization' => 'required|numeric|max:5',
            'activeness_Q2_realization' => 'required|numeric|max:1',
            'activeness_Q3_realization' => 'required|numeric|max:2',
            'ability_Q1_realization' => 'required|numeric|max:100',
            'communication_Q1_realization' => 'required|numeric|max:4',
            'communication_Q2_realization' => 'required|numeric|max:4',
            'discipline_Q1_realization' => 'required|numeric|max:100',
            'discipline_Q2_realization' => 'required|numeric|max:100',
            'discipline_Q3_realization' => 'required|numeric|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $kpiData = $request->only([
            'activeness_Q1_realization', 'activeness_Q2_realization', 'activeness_Q3_realization',
            'ability_Q1_realization',
            'communication_Q1_realization', 'communication_Q2_realization',
            'discipline_Q1_realization', 'discipline_Q2_realization', 'discipline_Q3_realization'
        ]);
        $kpiData['user_id'] = $id;
        $kpiData['year'] = $year;
        $kpiData['month'] = $month;

        try {
            $kpi = KPIRating::create($kpiData);
            return new KpiStaffResource($kpi);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json(['message' => 'KPI for this user, year, and month already exists.'], 409);
            }
            throw $e;
        }
    }

        /**
     * @OA\Get(
     *     path="/api/v1/kpi/supervisor-staff/{id}/{month}/score",
     *     summary="Supervisor melihat KPI seorang staff",
     *     description="Supervisor melihat KPI Rating seorang staff pada bulan yang ditentukan",
     *     tags={"KPI"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=17),
     *         description="ID staff yang KPI-nya ingin dilihat"
     *     ),
     *     @OA\Parameter(
     *         name="month",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=9),
     *         description="Bulan KPI yang ingin dilihat"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="KPI berhasil ditemukan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="string", example="17"),
     *                 @OA\Property(property="year", type="string", example="2024"),
     *                 @OA\Property(property="month", type="string", example="9"),
     *                 @OA\Property(property="kpi_data", type="array", @OA\Items(
     *                     @OA\Property(property="aspect", type="string", example="activeness"),
     *                     @OA\Property(property="total_aspect", type="integer", example=20),
     *                     @OA\Property(property="value_conversion_aspect", type="string", example="A"),
     *                     @OA\Property(property="type", type="array", @OA\Items(
     *                         @OA\Property(property="kpi", type="string", example="Q1"),
     *                         @OA\Property(property="end_of_month_realization", type="integer", example=5),
     *                         @OA\Property(property="score", type="integer", example=100),
     *                         @OA\Property(property="final_score", type="integer", example=10)
     *                     )),
     *                     @OA\Property(property="total_aspects", type="integer", example=100),
     *                     @OA\Property(property="value_conversion", type="string", example="Excellent")
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Tidak memiliki hak akses untuk melihat KPI staff ini.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized. This staff member is not under your supervision.")
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
     *         description="Terjadi kesalahan internal server.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */

    public function getStaffKpi($id, $month)
    {
        $userId = auth()->id();
        $staff = User::find($id);
        if (!$staff || ($staff->supervisor_id != $userId && $staff->vice_supervisor_id != $userId)) {
            return response()->json(['message' => 'Unauthorized. This staff member is not under your supervision.'], 403);
        }
        $year = date('Y');
        $kpi = KPIRating::where('user_id', $id)->where('year', $year)->where('month', $month)->first();
        if (!$kpi) {
            return response()->json(['message' => 'Data not found.'], 404);
        }

        return new KpiStaffResource($kpi);
    }
}
