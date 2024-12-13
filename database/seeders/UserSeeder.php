<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            // Users with C-Level roles
            [
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'email' => 'alice.johnson@example.com',
                'instagram' => 'alice_insta',
                'linkedin' => 'alice-linkedin',
                'batch_no' => 1,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => 1, // Chief Creative Officer
                'division_id' => null, // No division for C-Level
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => true,
                'Sflag' => false,
                'StFlag' => false,
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Bob',
                'last_name' => 'Smith',
                'email' => 'bob.smith@example.com',
                'instagram' => 'bob_insta',
                'linkedin' => 'bob-linkedin',
                'batch_no' => 1,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => 2, // Chief Development Officer
                'division_id' => null, // No division for C-Level
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => true,
                'Sflag' => false,
                'StFlag' => false,
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Users with Division roles
            [
                'first_name' => 'Charlie',
                'last_name' => 'Brown',
                'email' => 'charlie.brown@example.com',
                'instagram' => 'charlie_insta',
                'linkedin' => 'charlie-linkedin',
                'batch_no' => 2,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 1, // Social Media Specialist
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true,
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Diana',
                'last_name' => 'Prince',
                'email' => 'diana.prince@example.com',
                'instagram' => 'diana_insta',
                'linkedin' => 'diana-linkedin',
                'batch_no' => 2,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 6,
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => true,
                'StFlag' => false,
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Staf',
                'last_name' => 'Makan',
                'email' => 'staff-1@example.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => 2,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 3, // Content Writer
                'supervisor_id' => 4,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true,
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'William',
                'last_name' => 'Tukien',
                'email' => 'staff-2@example.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => 2,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 3, // Content Writer
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => true,
                'StFlag' => false,
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Admin',
                'last_name' => '1',
                'email' => 'admin.weekly.report@yopmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => 5,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => null, // Content Writer
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => true,
                'Sflag' => true,
                'StFlag' => true,
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('users')->insert($users);
    }
}