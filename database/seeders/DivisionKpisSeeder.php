<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionKpisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('division_kpis')->insert([
            [
                'division_id' => 1,
                'year' => '2024',
                'month' => 1,
                'task_name' => 'Design Website CC Careers',
                'weight' => 10,
                'target' => 95,
                'end_of_month_realization' => null,
                'created_at' => '2024-09-11 02:50:34',
                'updated_at' => '2024-09-11 02:50:34'
            ],
            [
                'division_id' => 1,
                'year' => '2024',
                'month' => 1,
                'task_name' => 'Another KPI',
                'weight' => 90,
                'target' => 95,
                'end_of_month_realization' => null,
                'created_at' => '2024-09-11 02:50:34',
                'updated_at' => '2024-09-11 02:50:34'
            ],
            [
                'division_id' => 1,
                'year' => '2024',
                'month' => 2,
                'task_name' => 'Membuat API CC Careers',
                'weight' => 10,
                'target' => 95,
                'end_of_month_realization' => null,
                'created_at' => '2024-09-11 02:50:41',
                'updated_at' => '2024-09-11 02:50:41'
            ],
            [
                'division_id' => 1,
                'year' => '2024',
                'month' => 2,
                'task_name' => 'Refactor Code',
                'weight' => 90,
                'target' => 95,
                'end_of_month_realization' => null,
                'created_at' => '2024-09-11 02:50:41',
                'updated_at' => '2024-09-11 02:50:41'
            ]
        ]);
    }
}
