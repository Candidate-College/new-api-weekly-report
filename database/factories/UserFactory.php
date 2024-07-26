<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'instagram' => $this->faker->url,
            'linkedin' => $this->faker->url,
            'batch_no' => $this->faker->year,
            'hashed_password' => bcrypt('password'),
            'email_verified_at' => now(),
            'division' => $this->faker->word,
            'supervisor_id' => null,
            'CFlag' => false,
            'SFlag' => false,
            'StFlag' => true,
            'profile_picture' => $this->faker->imageUrl,
        ];
    }

    public function clevel()
    {
        return $this->state(function (array $attributes) {
            return [
                'CFlag' => true,
                'SFlag' => false,
                'StFlag' => false,
                'supervisor_id' => null,
            ];
        });
    }

    public function supervisor(User $clevel)
    {
        return $this->state(function (array $attributes) use ($clevel) {
            return [
                'CFlag' => false,
                'SFlag' => true,
                'StFlag' => false,
                'supervisor_id' => $clevel->id,
            ];
        });
    }

    public function staff(User $supervisor)
    {
        return $this->state(function (array $attributes) use ($supervisor) {
            return [
                'CFlag' => false,
                'SFlag' => false,
                'StFlag' => true,
                'supervisor_id' => $supervisor->id,
            ];
        });
    }
}
