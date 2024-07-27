<?php

namespace Database\Seeders;

use App\Models\OTP;
use App\Models\User;
use Illuminate\Database\Seeder;

class OTPSeeder extends Seeder
{
    public function run()
    {
        User::all()->each(function ($user) {
            OTP::factory()->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
