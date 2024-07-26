<?php

use App\Models\KPIRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a KPIRating', function () {
    $user = User::factory()->create();
    $kpiRating = KPIRating::factory()->create([
        'user_id' => $user->id,
        'year' => 2023,
        'month' => 7,
        'activeness_Q1_score' => 8.5,
        'activeness_Q2_score' => 7.0,
        'activeness_Q3_score' => 9.0,
        'ability_Q1_score' => 8.0,
        'communication_Q1_score' => 7.5,
        'communication_Q2_score' => 8.5,
        'discipline_Q1_score' => 9.0,
        'discipline_Q2_score' => 8.0,
        'discipline_Q3_score' => 7.5,
    ]);

    expect($kpiRating)->toBeInstanceOf(KPIRating::class);
    expect($kpiRating->user_id)->toBe($user->id);
    expect($kpiRating->year)->toBe(2023);
    expect($kpiRating->month)->toBe(7);
    expect($kpiRating->activeness_Q1_score)->toBe(8.5);
});
