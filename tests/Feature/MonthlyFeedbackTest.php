<?php

use App\Models\User;
use App\Models\MonthlyFeedback;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create monthly feedback', function () {
    $user = User::factory()->create();

    $feedback = MonthlyFeedback::factory()->create([
        'user_id' => $user->id,
        'year' => 2024,
        'month' => 7,
        'content_text' => 'This is a feedback text.',
    ]);

    expect($feedback)->toBeInstanceOf(MonthlyFeedback::class);
    expect($feedback->user_id)->toBe($user->id);
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $feedback = MonthlyFeedback::factory()->create(['user_id' => $user->id]);

    expect($feedback->user)->toBeInstanceOf(User::class);
    expect($feedback->user->id)->toBe($user->id);
});
