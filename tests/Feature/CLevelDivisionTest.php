<?php

use App\Models\User;
use App\Models\Division;
use App\Models\CLevelDivision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

beforeEach(function () {
    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
});

test('c_level_division belongs to a user with CFlag true', function () {
    $cLevelUser = User::factory()->create(['CFlag' => true]);
    $division = Division::factory()->create();

    $cLevelDivision = CLevelDivision::factory()->create([
        'c_level_id' => $cLevelUser->id,
        'division_id' => $division->id,
    ]);

    expect($cLevelDivision->user->id)->toBe($cLevelUser->id);
    expect($cLevelDivision->user->CFlag)->toBeTrue();
});

test('c_level_division belongs to a division', function () {
    $cLevelUser = User::factory()->create(['CFlag' => true]);
    $division = Division::factory()->create();

    $cLevelDivision = CLevelDivision::factory()->create([
        'c_level_id' => $cLevelUser->id,
        'division_id' => $division->id,
    ]);

    expect($cLevelDivision->division->id)->toBe($division->id);
});
