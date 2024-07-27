<?php

use App\Models\User;
use App\Models\MonthlyFeedback;
use App\Models\OTP;
use App\Models\DailyReport;
use App\Models\KPIRating;
use Database\Seeders\DatabaseSeeder;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

test('database seeder creates correct number of records', function () {
    $seeder = new DatabaseSeeder();
    $seeder->run();

    expect(User::count())->toBe(26)
        ->and(MonthlyFeedback::count())->toBe(156)
        ->and(OTP::count())->toBe(26)
        ->and(DailyReport::count())->toBe(780)
        ->and(KPIRating::count())->toBe(312);
});
