<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class DailyReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();

        $data = [];

        for ($i = 1; $i <= 65; $i++) {
            for ($j = 0; $j < rand(3, 5); $j++) {
                $createdAt = $faker->dateTimeBetween('-6 months', 'now');
                $lastUpdatedAt = Carbon::parse($createdAt)->addDays(rand(1, 30));

                $data[] = [
                    'user_id' => $i,
                    'created_at' => $createdAt,
                    'content_text' => $faker->paragraph(2),
                    'content_photo' => $faker->imageUrl(640, 480, 'business', true), // Gambar placeholder
                    'last_updated_at' => $lastUpdatedAt,
                ];
            }
        }

        // Insert ke database
        DB::table('daily_reports')->insert($data);
    }
}
