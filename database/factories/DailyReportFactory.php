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
            'content_text' => $this->faker->paragraph,
            'content_photo' => $this->faker->imageUrl,
            'timestamp' => now(),
        ];
    }
}
