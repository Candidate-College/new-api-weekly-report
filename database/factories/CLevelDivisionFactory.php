<?php

namespace Database\Factories;

use App\Models\CLevelDivision;
use App\Models\User;
use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class CLevelDivisionFactory extends Factory
{
    protected $model = CLevelDivision::class;

    public function definition()
    {
        return [
            'c_level_id' => User::factory()->create(['CFlag' => true])->id,
            'division_id' => Division::factory()->create()->id,
        ];
    }
}
