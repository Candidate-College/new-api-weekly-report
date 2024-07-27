<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create a C-level user (6 c-level users and it's vice c-level)
        User::factory()->count(12 * 2)->create([
            'CFlag' => true,
            'Sflag' => false,
            'StFlag' => false,
        ]);

        // Create some supervisors (18 subdivision with 2 supervisors with 1.3 more load for future expansion)
        User::factory()->count(ceil(18 * 2 * 1.3))->create([
            'CFlag' => false,
            'Sflag' => true,
            'StFlag' => false,
        ]);

        // Create regular staff (100 staff with 1.3 more load for future expansion)
        User::factory()->count(ceil(100 * 1.3))->create([
            'CFlag' => false,
            'Sflag' => false,
            'StFlag' => true,
        ]);

        // Assign supervisors and vice supervisors for staff
        $staff = User::where('StFlag', true)->get();
        $supervisors = User::where('Sflag', true)->get();
        $cLevel = User::where('CFlag', true)->first();

        // Assign supervisors and vice supervisors for staff
        $staff->each(function ($user) use ($supervisors) {
            $user->supervisor()->associate($supervisors->random());
            $user->viceSupervisor()->associate($supervisors->random());
            $user->save();
        });

        // Assign c-level and vice c-level for supervisors
        $supervisors->each(function ($supervisor) use ($cLevel) {
            $supervisor->supervisor()->associate($cLevel->random());
            $supervisor->viceSupervisor()->associate($cLevel->random());
            $supervisor->save();
        });
    }
}
