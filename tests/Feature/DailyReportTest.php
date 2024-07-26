<?php

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a DailyReport', function () {
    $user = User::factory()->create();
    $dailyReport = DailyReport::factory()->create([
        'user_id' => $user->id,
        'content_text' => 'This is a test report.',
        'content_photo' => 'https://example.com/photo.jpg',
        'timestamp' => now(),
    ]);

    expect($dailyReport)->toBeInstanceOf(DailyReport::class);
    expect($dailyReport->user_id)->toBe($user->id);
    expect($dailyReport->content_text)->toBe('This is a test report.');
    expect($dailyReport->content_photo)->toBe('https://example.com/photo.jpg');
});
