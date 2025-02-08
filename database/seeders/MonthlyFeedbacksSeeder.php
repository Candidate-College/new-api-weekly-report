<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonthlyFeedbacksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $feedbacks = [];
        $currentTimestamp = now();

        for ($userId = 1; $userId <= 65; $userId++) {
            for ($month = 1; $month <= 12; $month++) {
                $feedbacks[] = [
                    'user_id' => $userId,
                    'year' => '2024',
                    'month' => $month,
                    'content_text' => fake()->paragraph(),
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp,
                ];
            }
        }

        DB::table('monthly_feedbacks')->insert($feedbacks);
    }
}
