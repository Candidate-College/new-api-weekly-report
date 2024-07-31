<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
{
   
    $clevels = User::factory()->count(6)->clevel()->create();
    $supervisors = User::factory()->count(10)->supervisor($clevels->random())->create();
    $supervisors->each(function ($supervisor) use ($clevels) {
        $supervisor->update([
            'supervisor_id' => $clevels->random()->id,
            'vice_supervisor_id' => $clevels->random()->id,
            'CFlag' => false,
            'Sflag' => true,
            'StFlag' => false,
        ]);
    });

    $staffs = User::factory()->count(14)->create();
    $staffs->each(function ($staff) use ($supervisors) {
        $staff->update([
            'supervisor_id' => $supervisors->random()->id,
            'vice_supervisor_id' => $supervisors->random()->id,
            'CFlag' => false,
            'Sflag' => false,
            'StFlag' => true,
        ]);
    });
}}