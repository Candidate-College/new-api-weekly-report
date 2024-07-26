<?php

namespace Database\Factories;

use App\Models\OTP;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OtpFactory extends Factory
{
    protected $model = OTP::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'OTP_code' => Str::random(6),
            'expiration_time' => $this->faker->dateTimeBetween('now', '+1 hour'),
        ];
    }
}
