<?php

namespace Database\Seeders;

use App\Models\MonthlyFeedback;
use App\Models\User;
use Illuminate\Database\Seeder;

class MonthlyFeedbackSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            for ($i = 1; $i <= 4; $i++) {
                MonthlyFeedback::factory()->create([
                    'user_id' => $user->id,
                    'year' => now()->year,
                    'month' => $i,
                ]);
            }
        }
    }
}
