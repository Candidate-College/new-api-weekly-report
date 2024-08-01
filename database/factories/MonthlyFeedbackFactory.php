<?php

namespace Database\Factories;

use App\Models\MonthlyFeedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonthlyFeedbackFactory extends Factory
{
    protected $model = MonthlyFeedback::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'year' => $this->faker->year(),
            'month' => $this->faker->numberBetween(1, 12),
            'content_text' => $this->faker->paragraph,
        ];
    }
}
