<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CLevel;
use App\Models\Division;

class CLevelDivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Retrieve the CLevel and Division records efficiently
        $cto = CLevel::where('name', 'Chief Technology Officer')->first();
        $cdo = CLevel::where('name', 'Chief Development Officer')->first();
        $coo = CLevel::where('name', 'Chief Operating Officer')->first();
        $cco = CLevel::where('name', 'Chief Creative Officer')->first();
        $cpd = CLevel::where('name', 'Chief People Development')->first();
        $cro = CLevel::where('name', 'Chief Relation Officer')->first();

        // Retrieve the relevant divisions
        $divisions = [
            'Web Design' => Division::where('name', 'Web Design')->first(),
            'Frontend Developer' => Division::where('name', 'Frontend Developer')->first(),
            'Backend Developer' => Division::where('name', 'Backend Developer')->first(),
            'Quality Assurance' => Division::where('name', 'Quality Assurance')->first(),
            'DevOps' => Division::where('name', 'DevOps')->first(),
            'Business Development' => Division::where('name', 'Business Development')->first(),
            'Marketing' => Division::where('name', 'Marketing')->first(),
            'Social Media Specialist' => Division::where('name', 'Social Media Specialist')->first(),
            'Content Writer' => Division::where('name', 'Content Writer')->first(),
            'Event Crew' => Division::where('name', 'Event Crew')->first(),
            'Social Media Editor' => Division::where('name', 'Social Media Editor')->first(),
            'Podcast' => Division::where('name', 'Podcast')->first(),
            'Event Organizer' => Division::where('name', 'Event Organizer')->first(),
            'Talent Engagement' => Division::where('name', 'Talent Engagement')->first(),
            'People and Culture' => Division::where('name', 'People and Culture')->first(),
            'Secretary' => Division::where('name', 'Secretary')->first(),
            'Community Partner' => Division::where('name', 'Community Partner')->first(),
            'Brand Ambassador Supervisor' => Division::where('name', 'Brand Ambassador Supervisor')->first(),
            'Sponsorship' => Division::where('name', 'Sponsorship')->first(),
        ];

        // Prepare data for inserting into the pivot table
        $data = [];

        // Attach CTO divisions
        foreach (['Web Design', 'Frontend Developer', 'Backend Developer', 'Quality Assurance', 'DevOps'] as $divisionName) {
            $data[] = [
                'c_level_id' => $cto->id,
                'division_id' => $divisions[$divisionName]->id,
            ];
        }

        // Attach CDO divisions
        foreach (['Business Development', 'Marketing'] as $divisionName) {
            $data[] = [
                'c_level_id' => $cdo->id,
                'division_id' => $divisions[$divisionName]->id,
            ];
        }

        // Attach COO divisions
        foreach (['Social Media Specialist', 'Content Writer', 'Event Crew'] as $divisionName) {
            $data[] = [
                'c_level_id' => $coo->id,
                'division_id' => $divisions[$divisionName]->id,
            ];
        }

        // Attach CCO divisions
        foreach (['Social Media Editor', 'Podcast', 'Event Organizer', 'Talent Engagement'] as $divisionName) {
            $data[] = [
                'c_level_id' => $cco->id,
                'division_id' => $divisions[$divisionName]->id,
            ];
        }

        // Attach CPD divisions
        foreach (['People and Culture', 'Secretary', 'Community Partner'] as $divisionName) {
            $data[] = [
                'c_level_id' => $cpd->id,
                'division_id' => $divisions[$divisionName]->id,
            ];
        }

        // Attach CRO divisions
        foreach (['Brand Ambassador Supervisor', 'Sponsorship'] as $divisionName) {
            $data[] = [
                'c_level_id' => $cro->id,
                'division_id' => $divisions[$divisionName]->id,
            ];
        }

        // Insert data into the pivot table
        DB::table('c_level_divisions')->insert($data);
    }
}
