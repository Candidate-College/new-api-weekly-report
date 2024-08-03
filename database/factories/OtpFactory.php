<?php

namespace Database\Factories;

use App\Models\OTP;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OTPFactory extends Factory
{
    protected $model = OTP::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'created_at' => now(),
            'expiration_time' => now()->addMinutes(10),
           'OTP_code' => (string)$this->faker->numerify('####'),
        ];
    }
}