<?php

namespace Database\Factories;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailyReportFactory extends Factory
{
    protected $model = DailyReport::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'created_at' => $this->faker->dateTimeThisYear(),
            'content_text' => $this->faker->paragraph,
            'content_photo' => $this->faker->imageUrl(),
            'last_updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
