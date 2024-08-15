<?php

namespace Database\Seeders;

use App\Models\KPIRating;
use App\Models\User;
use Illuminate\Database\Seeder;

class KPIRatingSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            foreach (range(1, 4) as $month) {
                KPIRating::factory()->create([
                    'user_id' => $user->id,
                    'year' => now()->year,
                    'month' => $month,
                ]);
            }
        }
    }
}
