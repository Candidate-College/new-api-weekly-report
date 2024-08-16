<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Division;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run()
    {
        $divisions = Division::all();

        // Create 6 CLevel users and assign them to divisions
        $clevels = User::factory()->count(6)->clevel()->create()->each(function ($clevel) use ($divisions) {
            $clevel->division_id = null;
            $clevel->save();

            DB::table('c_level_divisions')->insert([
                'c_level_id' => $clevel->id,
                'division_id' => $divisions->random()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $supervisors = User::factory()->count(6)->supervisor($clevels->random())->create();
        $supervisors->each(function ($supervisor) use ($clevels) {
            $supervisor->update([
                'supervisor_id' => $clevels->random()->id,
                'vice_supervisor_id' => $clevels->random()->id,
                'CFlag' => false,
                'Sflag' => true,
                'StFlag' => false,
            ]);
        });
    
        $staffs = User::factory()->count(10)->create();
        $staffs->each(function ($staff) use ($supervisors) {
            $staff->update([
                'supervisor_id' => $supervisors->random()->id,
                'vice_supervisor_id' => $supervisors->random()->id,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true,
            ]);
        });
    }
}
