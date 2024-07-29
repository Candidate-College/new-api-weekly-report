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

test('daily report has custom timestamps', function () {
    $report = new DailyReport();
    $report->created_at = now();
    $report->last_updated_at = now();

    expect($report->created_at)->not->toBeNull()
        ->and($report->last_updated_at)->not->toBeNull()
        ->and($report->timestamps)->toBeFalse();
});

