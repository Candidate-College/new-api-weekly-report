<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KpisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('kpis')->insert([
            [
                'user_id' => 1,
                'year' => '2024',
                'month' => 1,
                'activeness_Q1_realization' => 3.06,
                'activeness_Q2_realization' => 3.95,
                'activeness_Q3_realization' => 8.49,
                'ability_Q1_realization' => 3.34,
                'communication_Q1_realization' => 4.81,
                'communication_Q2_realization' => 5.58,
                'discipline_Q1_realization' => 6.73,
                'discipline_Q2_realization' => 5.64,
                'discipline_Q3_realization' => 4.95,
                'created_at' => '2024-09-04 22:55:12',
                'updated_at' => '2024-09-04 22:55:12'
            ],
            [
                'user_id' => 1,
                'year' => '2024',
                'month' => 2,
                'activeness_Q1_realization' => 7.23,
                'activeness_Q2_realization' => 3.94,
                'activeness_Q3_realization' => 1.81,
                'ability_Q1_realization' => 6.94,
                'communication_Q1_realization' => 3.98,
                'communication_Q2_realization' => 3.61,
                'discipline_Q1_realization' => 1.88,
                'discipline_Q2_realization' => 9.77,
                'discipline_Q3_realization' => 4.92,
                'created_at' => '2024-09-04 22:55:12',
                'updated_at' => '2024-09-04 22:55:12'
            ],
            [
                'user_id' => 1,
                'year' => '2024',
                'month' => 3,
                'activeness_Q1_realization' => 3.03,
                'activeness_Q2_realization' => 2.82,
                'activeness_Q3_realization' => 4.55,
                'ability_Q1_realization' => 9.24,
                'communication_Q1_realization' => 9.43,
                'communication_Q2_realization' => 8.91,
                'discipline_Q1_realization' => 4.95,
                'discipline_Q2_realization' => 2.96,
                'discipline_Q3_realization' => 3.24,
                'created_at' => '2024-09-04 22:55:12',
                'updated_at' => '2024-09-04 22:55:12'
            ],
            [
                'user_id' => 1,
                'year' => '2024',
                'month' => 4,
                'activeness_Q1_realization' => 6.58,
                'activeness_Q2_realization' => 2.43,
                'activeness_Q3_realization' => 6.65,
                'ability_Q1_realization' => 0.95,
                'communication_Q1_realization' => 0.9,
                'communication_Q2_realization' => 7.18,
                'discipline_Q1_realization' => 7.07,
                'discipline_Q2_realization' => 2.42,
                'discipline_Q3_realization' => 4.75,
                'created_at' => '2024-09-04 22:55:12',
                'updated_at' => '2024-09-04 22:55:12'
            ],
            [
                'user_id' => 2,
                'year' => '2024',
                'month' => 1,
                'activeness_Q1_realization' => 5.32,
                'activeness_Q2_realization' => 1.8,
                'activeness_Q3_realization' => 5.28,
                'ability_Q1_realization' => 2.69,
                'communication_Q1_realization' => 6.69,
                'communication_Q2_realization' => 2.83,
                'discipline_Q1_realization' => 7.41,
                'discipline_Q2_realization' => 9.66,
                'discipline_Q3_realization' => 2.26,
                'created_at' => '2024-09-04 22:55:12',
                'updated_at' => '2024-09-04 22:55:12'
            ],
            [
                'user_id' => 2,
                'year' => '2024',
                'month' => 2,
                'activeness_Q1_realization' => 9.42,
                'activeness_Q2_realization' => 8.26,
                'activeness_Q3_realization' => 5.6,
                'ability_Q1_realization' => 6.6,
                'communication_Q1_realization' => 8.79,
                'communication_Q2_realization' => 0.11,
                'discipline_Q1_realization' => 9.52,
                'discipline_Q2_realization' => 1.6,
                'discipline_Q3_realization' => 1.14,
                'created_at' => '2024-09-04 22:55:12',
                'updated_at' => '2024-09-04 22:55:12'
            ],
            [
                'user_id' => 2,
                'year' => '2024',
                'month' => 3,
                'activeness_Q1_realization' => 5.59,
                'activeness_Q2_realization' => 9.03,
                'activeness_Q3_realization' => 4.8,
                'ability_Q1_realization' => 7.76,
                'communication_Q1_realization' => 1.29,
                'communication_Q2_realization' => 9.61,
                'discipline_Q1_realization' => 3.39,
                'discipline_Q2_realization' => 0.1,
                'discipline_Q3_realization' => 7.49,
                'created_at' => '2024-09-04 22:55:12',
                'updated_at' => '2024-09-04 22:55:12'
            ],
            [
                'user_id' => 2,
                'year' => '2024',
                'month' => 4,
                'activeness_Q1_realization' => 6.62,
                'activeness_Q2_realization' => 7.15,
                'activeness_Q3_realization' => 3,
                'ability_Q1_realization' => 5.15,
                'communication_Q1_realization' => 8.35,
                'communication_Q2_realization' => 0.73,
                'discipline_Q1_realization' => 9.7,
                'discipline_Q2_realization' => 2.09,
                'discipline_Q3_realization' => 0.77,
                'created_at' => '2024-09-04 22:55:12',
                'updated_at' => '2024-09-04 22:55:12'
            ]
        ]);
    }
}
