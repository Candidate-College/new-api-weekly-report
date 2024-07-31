<?php

use App\Models\User;
use App\Models\MonthlyFeedback;
use App\Models\OTP;
use App\Models\DailyReport;
use App\Models\KPIRating;
use Database\Seeders\DatabaseSeeder;


test('database seeder creates correct number of records', function () {
    $seeder = new DatabaseSeeder();
    $seeder->run();

    expect(User::count())->toBe(30)
        ->and(MonthlyFeedback::count())->toBe(360)
        ->and(OTP::count())->toBe(30)
        ->and(DailyReport::count())->toBe(600)
        ->and(KPIRating::count())->toBe(360);
});
