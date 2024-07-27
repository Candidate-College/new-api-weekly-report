<?php

namespace Database\Seeders;

use App\Models\MonthlyFeedback;
use App\Models\User;
use Illuminate\Database\Seeder;

class MonthlyFeedbackSeeder extends Seeder
{
    public function run()
    {
        User::all()->each(function ($user) {
            MonthlyFeedback::factory()->count(6)->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
