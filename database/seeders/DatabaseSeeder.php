<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            CLevelSeeder::class,
            DivisionSeeder::class,
            CLevelDivisionSeeder::class,
            UserSeeder::class,
            DailyReportsSeeder::class,
            DivisionKpisSeeder::class,
            KpisSeeder::class,
            MonthlyFeedbacksSeeder::class,
        ]);
    }
}
