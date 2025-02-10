<?php

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Support\Facades\DB;

    beforeEach(function () {
        DB::beginTransaction();
    });

    afterEach(function () {
        DB::rollBack();
    });

test('daily report factory creates valid report', function () {
    $supervisors = User::where('Sflag', true)->get();
    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);
    $report = DailyReport::factory()->create(['user_id' => $staff->id]);
    expect($report)->toBeInstanceOf(DailyReport::class)
        ->and($report->content_text)->not->toBeNull();
});

test('daily report belongs to a user', function () {
    $supervisors = User::where('Sflag', true)->get();

    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);

    $report = DailyReport::factory()->create(['user_id' => $staff->id]);

    expect($report->staff)->toBeInstanceOf(User::class)
        ->and($report->staff->id)->toBe($staff->id);
});

test('daily report attributes are set correctly', function () {
    $supervisors = User::where('Sflag', true)->get();
    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);
    $report = DailyReport::factory()->create(['user_id' => $staff->id]);

    expect($report->content_text)->toBeString()
        ->and($report->content_photo)->toBeString()
        ->and($report->created_at)->toBeInstanceOf(\DateTime::class)
        ->and($report->last_updated_at)->toBeInstanceOf(\DateTime::class);
});

test('daily report has correct primary key', function () {
    $supervisors = User::where('Sflag', true)->get();
    $staff = User::factory()->create([
        'supervisor_id' => $supervisors->random()->id,
        'vice_supervisor_id' => $supervisors->random()->id,
        'CFlag' => false,
        'Sflag' => false,
        'StFlag' => true,
    ]);
    $report = DailyReport::factory()->create(['user_id' => $staff->id]);

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

