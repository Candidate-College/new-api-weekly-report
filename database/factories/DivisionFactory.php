<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Division;

class DivisionFactory extends Factory
{
    protected $model = Division::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
        ];
    }
}

