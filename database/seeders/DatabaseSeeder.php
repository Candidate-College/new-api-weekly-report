<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            DivisionSeeder::class,
            UserSeeder::class,
            MonthlyFeedbackSeeder::class,
            OTPSeeder::class,
            DailyReportSeeder::class,
            KPIRatingSeeder::class,
        ]);
    }
}
