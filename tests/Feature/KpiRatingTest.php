<?php

use App\Models\User;
use App\Models\KPIRating;

test('KPI factory creates valid KPI', function () {
    $supervisors = User::where('Sflag', true)->get();
    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);

    $kpi = KPIRating::factory()->create(['user_id' => $staff->id]);
    
    expect($kpi)->toBeInstanceOf(KPIRating::class)
        ->and($kpi->activeness_Q1_score)->not->toBeNull();
});

test('KPI belongs to a user', function () {
    $supervisors = User::where('Sflag', true)->get();
    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);
    $kpi = KPIRating::factory()->create(['user_id' => $staff->id]);

    expect($kpi->user)->toBeInstanceOf(User::class)
        ->and($kpi->user->id)->toBe($staff->id);
});

test('KPI attributes are set correctly', function () {
    
    $supervisors = User::where('Sflag', true)->get();
    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);
    $kpi = KPIRating::factory()->create(['user_id' => $staff->id]);

    expect($kpi->year)->toBeString()
        ->and($kpi->month)->toBeInt()->toBeGreaterThanOrEqual(1)->toBeLessThanOrEqual(12)
        ->and($kpi->activeness_Q1_score)->toBeFloat()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(10)
        ->and($kpi->activeness_Q2_score)->toBeFloat()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(10)
        ->and($kpi->activeness_Q3_score)->toBeFloat()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(10)
        ->and($kpi->ability_Q1_score)->toBeFloat()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(10)
        ->and($kpi->communication_Q1_score)->toBeFloat()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(10)
        ->and($kpi->communication_Q2_score)->toBeFloat()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(10)
        ->and($kpi->discipline_Q1_score)->toBeFloat()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(10)
        ->and($kpi->discipline_Q2_score)->toBeFloat()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(10)
        ->and($kpi->discipline_Q3_score)->toBeFloat()->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(10);
});

test('KPI has correct primary key', function () {
    $supervisors = User::where('Sflag', true)->get();
    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);
    $kpi = KPIRating::factory()->create(['user_id' => $staff->id]);
    
    expect($kpi->getKeyName())->toBe(['user_id', 'year', 'month'])
        ->and($kpi->incrementing)->toBeFalse();
});