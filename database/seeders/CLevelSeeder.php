<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CLevel;
use Carbon\Carbon;

class CLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $levels = [
            ['name' => 'Chief Creative Officer', 'abbreviation' => 'CCO', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Chief Development Officer', 'abbreviation' => 'CDO', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Chief Operating Officer', 'abbreviation' => 'COO', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Chief People Development', 'abbreviation' => 'CPD', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Chief Relation Officer', 'abbreviation' => 'CRO', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Chief Technology Officer', 'abbreviation' => 'CTO', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];

        CLevel::insert($levels); // Use insert for bulk insertion
    }
}
