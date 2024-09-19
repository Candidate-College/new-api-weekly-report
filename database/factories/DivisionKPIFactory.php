<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\DivisionKPI;
use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionKPIFactory extends Factory
{
    protected $model = DivisionKPI::class;

    public function definition()
    {
        return [
            'division_id' => Division::factory(),
            'year' => $this->faker->year,
            'month' => $this->faker->month,
            'task_name' => $this->faker->sentence,
            'weight' => $this->faker->randomFloat(2, 0, 1),
            'target' => $this->faker->randomNumber(),
            'end_of_month_realization' => $this->faker->randomNumber(),
        ];
    }
}

