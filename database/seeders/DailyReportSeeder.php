<?php

namespace Database\Seeders;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Database\Seeder;

class DailyReportSeeder extends Seeder
{
    public function run()
    {
        User::all()->each(function ($user) {
            DailyReport::factory()->count(20)->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
