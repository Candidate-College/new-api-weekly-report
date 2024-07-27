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
            'vice_supervisor_id' => null,
            'remember_token' => Str::random(10),
            'CFlag' => false,
            'SFlag' => false,
            'StFlag' => true,
            'profile_picture' => $this->faker->imageUrl,
        ];
    }

    public function clevel()
    {
        return $this->state(function () {
            return [
                'CFlag' => true,
                'SFlag' => false,
                'StFlag' => false,
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
            ];
        });
    }

    public function supervisor(User $clevel)
    {
        return $this->state(function () use ($clevel) {
            return [
                'CFlag' => false,
                'SFlag' => true,
                'StFlag' => false,
                'supervisor_id' => $clevel->id,
                'vice_supervisor_id' => null,
            ];
        });
    }

    public function staff(User $supervisor, User $vice_supervisor)
    {
        return $this->state(function () use ($supervisor, $vice_supervisor) {
            return [
                'CFlag' => false,
                'SFlag' => false,
                'StFlag' => true,
                'supervisor_id' => $supervisor->id,
                'vice_supervisor_id' => $vice_supervisor->id,
            ];
        });
    }
}
