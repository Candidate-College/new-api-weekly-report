<?php

use App\Models\KPIRating;
use App\Models\User;


test('KPI factory creates valid KPI', function () {
    $kpi = KPIRating::factory()->create();
    expect($kpi)->toBeInstanceOf(KPIRating::class)
        ->and($kpi->activeness_Q1_score)->not->toBeNull();
});

test('KPI belongs to a user', function () {
    $user = User::factory()->create();
    $kpi = KPIRating::factory()->create(['user_id' => $user->id]);

    expect($kpi->user)->toBeInstanceOf(User::class)
        ->and($kpi->user->id)->toBe($user->id);
});

test('KPI attributes are set correctly', function () {
    $kpi = KPIRating::factory()->create();

    expect($kpi->year)->toBeInt()
        ->and($kpi->month)->toBeInt()
        ->and($kpi->activeness_Q1_score)->toBeFloat()
        ->and($kpi->activeness_Q2_score)->toBeFloat()
        ->and($kpi->activeness_Q3_score)->toBeFloat()
        ->and($kpi->ability_Q1_score)->toBeFloat()
        ->and($kpi->communication_Q1_score)->toBeFloat()
        ->and($kpi->communication_Q2_score)->toBeFloat()
        ->and($kpi->discipline_Q1_score)->toBeFloat()
        ->and($kpi->discipline_Q2_score)->toBeFloat()
        ->and($kpi->discipline_Q3_score)->toBeFloat();
});

test('KPI has correct primary key', function () {
    $kpi = KPIRating::factory()->create();

    expect($kpi->getKeyName())->toBe(['user_id', 'year', 'month'])
        ->and($kpi->incrementing)->toBeFalse();
});
