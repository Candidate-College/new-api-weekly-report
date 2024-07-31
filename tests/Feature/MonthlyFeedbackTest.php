<?php

use App\Models\MonthlyFeedback;
use App\Models\User;


test('monthly feedback factory creates valid feedback', function () {
    $supervisors = User::where('Sflag', true)->get();
    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);
    $feedback = MonthlyFeedback::factory()->create(['user_id' => $staff->id]);
    expect($feedback)->toBeInstanceOf(MonthlyFeedback::class)
        ->and($feedback->content_text)->not->toBeNull();
});

test('monthly feedback belongs to a user', function () {
    $supervisors = User::where('Sflag', true)->get();
    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);
    $feedback = MonthlyFeedback::factory()->create(['user_id' => $staff->id]);

    expect($feedback->user)->toBeInstanceOf(User::class)
        ->and($feedback->user->id)->toBe($staff->id);
});

test('monthly feedback attributes are set correctly', function () {
    $supervisors = User::where('Sflag', true)->get();
    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);
    $feedback = MonthlyFeedback::factory()->create(['user_id' => $staff->id]);

    expect($feedback->year)->toBeString()
        ->and($feedback->month)->toBeInt()
        ->and($feedback->content_text)->toBeString();
});

test('monthly feedback has correct primary key', function () {
    $feedback = new MonthlyFeedback();

    expect($feedback->getKeyName())->toBe(['user_id', 'year', 'month'])
        ->and($feedback->incrementing)->toBeFalse();
});
