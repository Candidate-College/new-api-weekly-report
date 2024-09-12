<?php

use App\Models\User;
use App\Models\Division;
use App\Models\CLevelDivision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

it('should verify that CLevel has a division', function () {
    $cLevels = User::where('CFlag', true)->get();
    foreach ($cLevels as $cLevel) {
        $cLevelDivisions = CLevelDivision::where('c_level_id', $cLevel->id)->get();
        expect($cLevelDivisions)->not()->toBeEmpty();
        
        foreach ($cLevelDivisions as $cLevelDivision) {
            $division = Division::find($cLevelDivision->division_id);
            
            expect($division)->not()->toBeNull();
        }
    }
});
