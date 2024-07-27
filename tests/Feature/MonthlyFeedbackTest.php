<?php

use App\Models\MonthlyFeedback;
use App\Models\User;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

test('monthly feedback factory creates valid feedback', function () {
    $feedback = MonthlyFeedback::factory()->create();
    expect($feedback)->toBeInstanceOf(MonthlyFeedback::class)
        ->and($feedback->content_text)->not->toBeNull();
});

test('monthly feedback belongs to a user', function () {
    $user = User::factory()->create();
    $feedback = MonthlyFeedback::factory()->create(['user_id' => $user->id]);

    expect($feedback->user)->toBeInstanceOf(User::class)
        ->and($feedback->user->id)->toBe($user->id);
});

test('monthly feedback attributes are set correctly', function () {
    $feedback = MonthlyFeedback::factory()->create();

    expect($feedback->year)->toBeInt()
        ->and($feedback->month)->toBeInt()
        ->and($feedback->content_text)->toBeString();
});

test('monthly feedback has correct primary key', function () {
    $feedback = MonthlyFeedback::factory()->create();

    expect($feedback->getKeyName())->toBe(['user_id', 'year', 'month'])
        ->and($feedback->incrementing)->toBeFalse();
});
