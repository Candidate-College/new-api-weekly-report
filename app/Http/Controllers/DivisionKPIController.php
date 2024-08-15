<?php

namespace App\Http\Controllers;

use App\Models\DivisionKPI;
use Illuminate\Http\Request;
use App\Models\CLevelDivision;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\DivisionKPICollection;
use App\Http\Resources\ScoreDivisionKPICollection;

class DivisionKPIController extends Controller
{
        /**
     * @OA\Post(
     *     path="/api/v1/kpi/supervisor-division/{year}/{month}",
     *     summary="Supervisor mengisi KPI Divisi",
     *     description="Supervisor mengisi KPI dari divisinya per bulan dan akan dikirimkan ke C-Level untuk dinilai",
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
     *         @OA\Schema(type="integer", example=5),
     *         description="Bulan KPI"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="kpis",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="task_name", type="string", example="Design Website CC Careers"),
     *                     @OA\Property(property="weight", type="integer", example=10),
     *                     @OA\Property(property="target", type="integer", example=100)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="KPI berhasil disimpan.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="division_id", type="integer", example=1),
     *                     @OA\Property(property="year", type="string", example="2024"),
     *                     @OA\Property(property="month", type="integer", example=5),
     *                     @OA\Property(property="task_name", type="string", example="Design Website CC Careers"),
     *                     @OA\Property(property="weight", type="integer", example=10),
     *                     @OA\Property(property="target", type="integer", example=100),
     *                     @OA\Property(property="end_of_month_realization", type="integer", nullable=true, example=null)
     *                 )
     *             ),
     *             @OA\Property(property="total_weight", type="integer", example=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Format pengisian salah.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Incorrect Filling Format"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=423,
     *         description="Total bobot harus 100.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Total weight must be 100")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="KPI sudah ada dan tidak bisa diubah.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="KPIs for the given division, year, and month already exist and cannot be modified")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Token tidak valid.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token tidak valid")
     *         )
     *     )
     * )
     */

    public function createDivisionKPI(Request $request, $year, $month)
    {
        $divisionId = $request->user()->division_id;
        $existingKPI = DivisionKPI::where([
            'division_id' => $divisionId,
            'year' => $year,
            'month' => $month,
        ])->exists();
    
        if ($existingKPI) {
            return response()->json(['message' => 'KPIs for the given division, year, and month already exist and cannot be modified'], 403);
        }

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
            return response()->json(['message' => 'Total weight must be 100'], 423);
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
     *     path="/api/v1/kpi/supervisor-division/{year}/{month}",
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

    public function showDivisionKPI(Request $request, $year, $month)
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
    
        /**
     * @OA\Post(
     *     path="/api/v1/kpi/division/{divisionId}/{year}/{month}/score",
     *     summary="C-Level menilai KPI Divisi",
     *     description="C-Level menilai KPI divisi menggunakan realisasi akhir bulan divisinya pada tahun dan bulan tertentu.",
     *     tags={"KPI"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="divisionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="ID Divisi"
     *     ),
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
     *         @OA\Schema(type="integer", example=5),
     *         description="Bulan KPI"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="realizations",
     *                 type="array",
     *                 @OA\Items(
     *                     type="number",
     *                     example=90
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Realisasi KPI berhasil diperbarui.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="division_id", type="integer", example=1),
     *                     @OA\Property(property="year", type="string", example="2024"),
     *                     @OA\Property(property="month", type="integer", example=5),
     *                     @OA\Property(property="task_name", type="string", example="Design Website CC Careers"),
     *                     @OA\Property(property="weight", type="integer", example=10),
     *                     @OA\Property(property="target", type="integer", example=100),
     *                     @OA\Property(property="end_of_month_realization", type="integer", example=90)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Realisasi akhir bulan sudah ada atau Anda tidak memiliki otorisasi untuk memperbarui KPI ini.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You are not authorized to update this division or End-of-month realization already exists and cannot be modified")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="KPI tidak ditemukan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No KPIs found for the given division, year, and month")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Format pengisian salah atau realisasi tidak sesuai dengan target.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Incorrect Filling Format or Number of realizations must match the number of KPIs or End-of-month realization cannot exceed the target"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */

    public function updateScoreDivisionKPI(Request $request, $divisionId, $year, $month)
    {
        $userId = Auth::id();
        $validDivision = CLevelDivision::where([
            'c_level_id' => $userId,
            'division_id' => $divisionId,
        ])->exists();
    
        if (!$validDivision) {
            return response()->json(['message' => 'You are not authorized to update this division'], 403);
        }

        $kpis = DivisionKPI::where([
            'division_id' => $divisionId,
            'year' => $year,
            'month' => $month,
        ])->get();

        if ($kpis->isEmpty()) {
            return response()->json(['message' => 'No KPIs found for the given division, year, and month'], 404);
        }

        foreach ($kpis as $kpi) {
            if ($kpi->end_of_month_realization !== null) {
                return response()->json(['message' => 'End-of-month realization already exists and cannot be modified'], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'realizations' => 'required|array',
            'realizations.*' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Incorrect Filling Format', 'errors' => $validator->errors()], 422);
        }

        if (count($request->realizations) !== $kpis->count()) {
            return response()->json(['message' => 'Number of realizations must match the number of KPIs'], 422);
        }

        foreach ($kpis as $index => $kpi) {
            $realization = $request->realizations[$index];
            
            if ($realization > $kpi->target) {
                return response()->json(['message' => 'End-of-month realization cannot exceed the target'], 422);
            }
            
            DivisionKPI::where([
                'division_id' => $kpi->division_id,
                'year' => $kpi->year,
                'month' => $kpi->month,
                'task_name' => $kpi->task_name
            ])->update(['end_of_month_realization' => $realization]);
        }

        $updatedKpis = DivisionKPI::where([
            'division_id' => $divisionId,
            'year' => $year,
            'month' => $month,
        ])->get();

        return new ScoreDivisionKPICollection($updatedKpis);
    }

        /**
     * @OA\Get(
     *     path="/api/v1/kpi/division/{divisionId}/{year}/{month}/score",
     *     summary="Clevel Menampilkan score KPI divisi beserta realisasi akhir bulan",
     *     description="C-Level melihat KPI dari divisinya pada tahun dan bulan tertentu beserta realisasi akhir bulannya.",
     *     tags={"KPI"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="divisionId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="ID Divisi"
     *     ),
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
     *         @OA\Schema(type="integer", example=5),
     *         description="Bulan KPI"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="KPI berhasil ditampilkan.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="division_id", type="integer", example=1),
     *                     @OA\Property(property="year", type="string", example="2024"),
     *                     @OA\Property(property="month", type="integer", example=5),
     *                     @OA\Property(property="task_name", type="string", example="Design Website CC Careers"),
     *                     @OA\Property(property="weight", type="integer", example=10),
     *                     @OA\Property(property="target", type="integer", example=100),
     *                     @OA\Property(property="end_of_month_realization", type="integer", example=90)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Anda tidak memiliki otorisasi untuk melihat KPI ini.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You are not authorized to see this division")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="KPI tidak ditemukan.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Data not found.")
     *         )
     *     )
     * )
     */

    public function showScoreDivisionKPI(Request $request, $divisionId, $year, $month)
    {
        $userId = Auth::id();
        $validDivision = CLevelDivision::where([
            'c_level_id' => $userId,
            'division_id' => $divisionId,
        ])->exists();
    
        if (!$validDivision) {
            return response()->json(['message' => 'You are not authorized to see this division'], 403);
        }

        $kpis = DivisionKPI::where([
            'division_id' => $divisionId,
            'year' => $year,
            'month' => $month,
        ])->get();
        
        if ($kpis->isEmpty()) {
            return response()->json(['message' => 'Data not found.'], 404);
        }
    
        return new ScoreDivisionKPICollection($kpis);
    }
}
