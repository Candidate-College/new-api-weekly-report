<?php

use App\Models\User;
use App\Models\Division;
use App\Models\CLevelDivision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

it('should verify that CLevel has a division', function () {
    // Fetch all users with CFlag set to true
    $cLevels = User::where('CFlag', true)->get();

    // Iterate through each CLevel user and check their divisions
    foreach ($cLevels as $cLevel) {
        // Fetch associated divisions for this CLevel
        $cLevelDivisions = CLevelDivision::where('c_level_id', $cLevel->id)->get();

        // Ensure that there are divisions associated with the CLevel user
        expect($cLevelDivisions)->not()->toBeEmpty();
        
        foreach ($cLevelDivisions as $cLevelDivision) {
            $division = Division::find($cLevelDivision->division_id);
            
            // Ensure the division exists
            expect($division)->not()->toBeNull();
        }
    }
});
