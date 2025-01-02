<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;
use Carbon\Carbon;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $divisions = [
            ['name' => 'Social Media Specialist', 'abbreviation' => 'SMS', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Social Media Editor', 'abbreviation' => 'SME', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Content Writer', 'abbreviation' => 'CW', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Podcast', 'abbreviation' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Event Organizer', 'abbreviation' => 'EO', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Event Crew', 'abbreviation' => 'EC', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Talent Engagement', 'abbreviation' => 'TE', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'People and Culture', 'abbreviation' => 'PnC', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Secretary', 'abbreviation' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Brand Ambassador Supervisor', 'abbreviation' => 'BAS', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Community Partner', 'abbreviation' => 'ComPar', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Sponsorship', 'abbreviation' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Web Design', 'abbreviation' => 'UI/UX', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Frontend Developer', 'abbreviation' => 'FE', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Backend Developer', 'abbreviation' => 'BE', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Quality Assurance', 'abbreviation' => 'QA', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'DevOps', 'abbreviation' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Academic Development', 'abbreviation' => 'AcDev', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Treasurer', 'abbreviation' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Marketing', 'abbreviation' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Business Development', 'abbreviation' => 'BusDev', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];

        // Insert data into the divisions table
        Division::insert($divisions);
    }
}
