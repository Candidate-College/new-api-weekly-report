<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        \DB::unprepared(
            file_get_contents(base_path('database/db_new_api_weekly_report.sql'))
        );
    }
}
