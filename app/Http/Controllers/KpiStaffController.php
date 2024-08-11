<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\KPIRating;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\KpiStaffResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class KpiStaffController extends Controller
{
    private function isUserSupervisor($userId)
{
    $user = User::find($userId);
    return $user && $user->Sflag;
}
    public function kpiStaffCreate(Request $request, $id, $month)
    {
        $userId = auth()->id();
        $year = date('Y');
    
        if (!$this->isUserSupervisor($userId)) {
            return response()->json(['message' => 'Unauthorized. Only supervisors can create KPIs.'], 403);
        }

        $staff = User::find($id);
        if (!$staff || $staff->supervisor_id != $userId) {
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
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json(['message' => 'KPI for this user, year, and month already exists.'], 409);
            }
            throw $e;
        }
    }

    public function show($id, $month)
    {
        $year = date('Y');
        $kpi = KPIRating::where('user_id', $id)->where('year', $year)->where('month', $month)->firstOrFail();
        return new KpiStaffResource($kpi);
    }
}