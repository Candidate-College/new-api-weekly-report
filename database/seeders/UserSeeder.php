<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Division;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run()
    {
        \DB::unprepared(
            file_get_contents('.\database\db_new_api_weekly_report.sql')
        );
        // User::factory(10)->create();
    }
}
