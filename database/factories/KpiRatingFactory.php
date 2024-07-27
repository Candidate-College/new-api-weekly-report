<?php

namespace Database\Factories;

use App\Models\KPIRating;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class KpiRatingFactory extends Factory
{
    protected $model = KPIRating::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'year' => $this->faker->year,
            'month' => $this->faker->month,
            'activeness_Q1_score' => $this->faker->randomFloat(2, 0, 10),
            'activeness_Q2_score' => $this->faker->randomFloat(2, 0, 10),
            'activeness_Q3_score' => $this->faker->randomFloat(2, 0, 10),
            'ability_Q1_score' => $this->faker->randomFloat(2, 0, 10),
            'communication_Q1_score' => $this->faker->randomFloat(2, 0, 10),
            'communication_Q2_score' => $this->faker->randomFloat(2, 0, 10),
            'discipline_Q1_score' => $this->faker->randomFloat(2, 0, 10),
            'discipline_Q2_score' => $this->faker->randomFloat(2, 0, 10),
            'discipline_Q3_score' => $this->faker->randomFloat(2, 0, 10),
        ];
    }
}
