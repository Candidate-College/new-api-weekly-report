<?php

namespace App\Http\Controllers;

use App\Models\CLevel;
use App\Models\CLevelDivisionRelationship;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CLevelDivisionController extends Controller
{
    // create C Level Division
    public function createDivisionCLevel(Request $request) {
        $ClevelName = $request->c_level;
        $divisionName = $request->division;

        $existingCLevelDivision = CLevel::where([
            'name' => $ClevelName,
        ]);

        $existingDivisionName = Division::where([
            'name'=> $divisionName
        ]);

        if (!$existingCLevelDivision->exists()) {
            CLevel::create([
                'name' => $ClevelName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!$existingDivisionName->exists()) {
            Division::create([
                'name' => $divisionName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $cLevel = $existingCLevelDivision->first();
        $division = $existingDivisionName->first();
        CLevelDivisionRelationship::create([
            'c_level_id' => $cLevel->id,
            'division_id' => $division->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['message' => 'C Level Division created successfully'], 201);
    }

    // show c level division
    public function showDivisionCLevel(Request $request, $divisionId) {
        $cLevelDivision = CLevelDivisionRelationship::where([
            'division_id' => (int) $divisionId
        ])->get();

        if (!$cLevelDivision || $cLevelDivision->isEmpty()) {
            return response()->json(['message' => 'Data not found.'], 404);
        }

        return response()->json(['mesage' => 'Get Data Success', 'payload' => $cLevelDivision], 200);
    }

    // update c level division
    public function updateDivisionCLevel(Request $request, $divisionId) {

        $cLevelDivision = CLevelDivisionRelationship::where([
            'division_id' => $divisionId
        ])->first();

        if (!$cLevelDivision) {
            return response()->json(['message' => 'Data not found.'], 404);
        }

        $cLevelDivision->update($request->all());

        return response()->json(['mesage' => 'Update Success', 'payload' => $cLevelDivision], 201);
    }

    // delete c level division
    public function deleteDivisionCLevel(Request $request,$cLevelId , $divisionId) {
        $cLevelDivision = CLevelDivisionRelationship::where([
            'division_id' => $divisionId,
            'c_level_id' => $cLevelId
        ])->first();


        if (!$cLevelDivision) {
            return response()->json(['message' => 'Data not found.'], 404);
        }

        DB::table('c_level_divisions_relationship')
            ->where('c_level_id', $cLevelId)
            ->where('division_id', $divisionId)
            ->delete();

        return response()->json(['message' => 'Delete Success'], 200);
    }
}
