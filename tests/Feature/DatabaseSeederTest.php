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

    expect(User::count())->toBe(110)
        ->and(MonthlyFeedback::count())->toBe(156)
        ->and(OTP::count())->toBe(110)
        ->and(DailyReport::count())->toBe(3300)
        ->and(KPIRating::count())->toBe(1320);
});
