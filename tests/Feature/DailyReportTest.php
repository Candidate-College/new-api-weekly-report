<?php

use App\Models\DailyReport;
use App\Models\User;

test('daily report factory creates valid report', function () {
    $report = DailyReport::factory()->create();
    expect($report)->toBeInstanceOf(DailyReport::class)
        ->and($report->content_text)->not->toBeNull();
});

test('daily report belongs to a user', function () {
    $user = User::factory()->create();
    $report = DailyReport::factory()->create(['user_id' => $user->id]);

    expect($report->user)->toBeInstanceOf(User::class)
        ->and($report->user->id)->toBe($user->id);
});

test('daily report attributes are set correctly', function () {
    $report = DailyReport::factory()->create();

    expect($report->content_text)->toBeString()
        ->and($report->content_photo)->toBeString()
        ->and($report->created_at)->toBeInstanceOf(\DateTime::class)
        ->and($report->last_updated_at)->toBeInstanceOf(\DateTime::class);
});

test('daily report has correct primary key', function () {
    $report = DailyReport::factory()->create();

    expect($report->getKeyName())->toBe(['user_id', 'created_at'])
        ->and($report->incrementing)->toBeFalse();
});

test('daily report does not use timestamps', function () {
    $report = new DailyReport();
    expect($report->timestamps)->toBeFalse();
});
