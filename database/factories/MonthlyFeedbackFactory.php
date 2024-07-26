<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\MonthlyFeedback;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MonthlyFeedback>
 */
class MonthlyFeedbackFactory extends Factory
{
    protected $model = MonthlyFeedback::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'year' => $this->faker->year,
            'month' => $this->faker->month,
            'content_text' => $this->faker->paragraph,
        ];
    }
}