<?php

namespace Database\Seeders;

use App\Models\KPI;
use App\Models\User;
use App\Models\KPIRating;
use Illuminate\Database\Seeder;

class KPISeeder extends Seeder
{
    public function run()
    {
        User::all()->each(function ($user) {
            KPIRating::factory()->count(12)->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
