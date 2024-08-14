<?php

namespace App\Http\Controllers;

use App\Models\DivisionKPI;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\DivisionKPICollection;

class DivisionKPIController extends Controller
{
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
