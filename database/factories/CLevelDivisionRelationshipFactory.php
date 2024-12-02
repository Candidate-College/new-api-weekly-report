<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CLevelDivisionRelationship>
 */
class CLevelDivisionRelationshipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'c_level_id' => $this->faker->numberBetween(1, 5),
            'division_id' => $this->faker->numberBetween(1, 5),
        ];
    }
}
