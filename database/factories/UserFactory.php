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
            'instagram' => $this->faker->optional()->url,
            'linkedin' => $this->faker->optional()->url,
            'batch_no' => $this->faker->numberBetween(1, 10),
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'division_id' => $this->faker->numberBetween(1, 3),
            'supervisor_id' => null,
            'vice_supervisor_id' => null,
            'CFlag' => false,
            'Sflag' => false,
            'StFlag' => true,
            'profile_picture' => $this->faker->optional()->imageUrl(),
        ];
    }

    public function clevel()
    {
        return $this->state(function () {
            return [
                'CFlag' => true,
                'Sflag' => false,
                'StFlag' => false,
            ];
        });
    }

    public function supervisor(User $clevel)
    {
        return $this->state(function () use ($clevel) {
            return [
                'CFlag' => false,
                'Sflag' => true,
                'StFlag' => false,
                'supervisor_id' => $clevel->id,
            ];
        });
    }
    
    public function staff(User $supervisor, User $vice_supervisor)
    {
        return $this->state(function () use ($supervisor, $vice_supervisor) {
            return [
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true,
                'supervisor_id' => $supervisor->id,
                'vice_supervisor_id' => $vice_supervisor->id,
            ];
        });
    }
}
