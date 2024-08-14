<?php

namespace App\Http\Controllers;

use App\Models\DivisionKPI;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\DivisionKPICollection;

class DivisionKPIController extends Controller
{
        /**
     * @OA\Post(
     *     path="/api/v1/kpi/division/{year}/{month}",
     *     summary="Supervisor mengisi KPI Divisi",
     *     description="Supervisor mengisi KPI dari divisinya per bulan dan akan dikirimkan ke C-Level untuk dinilai.",
     *     tags={"KPI"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="2024"),
     *         description="Tahun KPI"
     *     ),
     *     @OA\Parameter(
     *         name="month",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="4"),
     *         description="Bulan KPI"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="kpis", type="array", @OA\Items(
     *                 @OA\Property(property="task_name", type="string", example="Design Website CC Careers"),
     *                 @OA\Property(property="weight", type="integer", example=10),
     *                 @OA\Property(property="target", type="integer", example=100)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="KPI berhasil diisi.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="division_id", type="integer", example=1),
     *                 @OA\Property(property="year", type="string", example="2024"),
     *                 @OA\Property(property="month", type="string", example="4"),
     *                 @OA\Property(property="task_name", type="string", example="Design Website CC Careers"),
     *                 @OA\Property(property="weight", type="integer", example=10),
     *                 @OA\Property(property="target", type="integer", example=100)
     *             )),
     *             @OA\Property(property="total_weight", type="integer", example=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Format pengisian salah atau total weight tidak 100.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Incorrect Filling Format"),
     *             @OA\Property(property="errors", type="object", additionalProperties={"type":"string"})
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

    public function CreateDivisionKPI(Request $request, $year, $month)
    {
        $divisionId = $request->user()->division_id;
        $validator = Validator::make([
            'year' => $year,
            'month' => $month,
            'kpis' => $request->kpis
        ], [
            'year' => 'required|integer',
            'month' => 'required|integer|between:1,12',
            'kpis' => 'required|array',
            'kpis.*.task_name' => 'required|string',
            'kpis.*.weight' => 'required|numeric',
            'kpis.*.target' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Incorrect Filling Format', 'errors' => $validator->errors()], 422);
        }

        $totalWeight = collect($request->kpis)->sum('weight');
        if ($totalWeight != 100) {
            return response()->json(['message' => 'Total weight must be 100'], 422);
        }

        DivisionKPI::where([
            'division_id' => $divisionId,
            'year' => $year,
            'month' => $month,
        ])->delete();

        // Simpan KPI baru
        $savedKPIs = collect($request->kpis)->map(function ($kpi) use ($divisionId, $year, $month) {
            return DivisionKPI::create([
                'division_id' => $divisionId,
                'year' => $year,
                'month' => $month,
                'task_name' => $kpi['task_name'],
                'weight' => $kpi['weight'],
                'target' => $kpi['target'],
            ]);
        });

        return new DivisionKPICollection($savedKPIs);
    }

        /**
     * @OA\Get(
     *     path="/api/v1/kpi/division/{year}/{month}",
     *     summary="Supervisor melihat KPI Divisi pada bulan yang dipilih",
     *     tags={"KPI"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="2024"),
     *         description="Tahun untuk KPI yang ingin ditampilkan"
     *     ),
     *     @OA\Parameter(
     *         name="month",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=4),
     *         description="Bulan untuk KPI yang ingin ditampilkan"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="KPI Divisi berhasil ditemukan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="division_id", type="integer", example=1),
     *                 @OA\Property(property="year", type="string", example="2024"),
     *                 @OA\Property(property="month", type="integer", example=4),
     *                 @OA\Property(property="task_name", type="string", example="Design Website CC Careers"),
     *                 @OA\Property(property="weight", type="integer", example=10),
     *                 @OA\Property(property="target", type="integer", example=100)
     *             )),
     *             @OA\Property(property="total_weight", type="integer", example=100)
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
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token tidak valid.")
     *         )
     *     )
     * )
     */

    public function ShowDivisionKPI(Request $request, $year, $month)
    {
        $divisionId = $request->user()->division_id;
    
        $kpis = DivisionKPI::where([
            'division_id' => $divisionId,
            'year' => $year,
            'month' => $month,
        ])->get();
        
        if ($kpis->isEmpty()) {
            return response()->json(['message' => 'Data not found.'], 404);
        }
    
        return new DivisionKPICollection($kpis);
    }
}
