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
            // Start Real Data
            // C-Level CTO
            [
                'first_name' => 'Fabian Juliansyah',
                'last_name' => 'Cahyadi',
                'email' => 'fabianjuliansyah89@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => 6, // C-Level ID 6
                'division_id' => null, // No specific division
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => true, // CFlag set to true
                'Sflag' => false,
                'StFlag' => false,
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // Staff CTO
            // Web Design
            [
                'first_name' => 'Muhammad Ilham',
                'last_name' => 'Takbir',
                'email' => 'muhamad.ilham.takbir24@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 13, // Division ID 13
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => true,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => false, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Dheivani Senjadipa',
                'last_name' => 'Senjadipa',
                'email' => 'dheivanisenja09@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 13, // Division ID 13
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => true,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => false, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Daffa Umar Thalib',
                'last_name' => 'Thalib',
                'email' => 'daffaumart@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 13, // Division ID 13
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Karimah Khairunnisa A',
                'last_name' => 'Khairunnisa A',
                'email' => 'karimahkhaerunnisa@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 13, // Division ID 13
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Muhammad Zamzam Fauzan',
                'last_name' => 'Fauzan',
                'email' => 'mhmmdzamzamfauzan@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 13, // Division ID 13
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Gabrielind Yoefita Aryanta Gultom',
                'last_name' => 'Gultom',
                'email' => 'g.yoefita@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 13, // Division ID 13
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Ferry Kurniawan',
                'last_name' => 'Kurniawan',
                'email' => 'fferrykurniawann@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 13, // Division ID 13
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // Frontend Developer
            [
                'first_name' => 'Alif Essa',
                'last_name' => 'Nurcahyani',
                'email' => 'alifessanurcahyani@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 14, // Division ID 14
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => true,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => false, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Edu Juanda',
                'last_name' => 'Pratama',
                'email' => 'edu.123320012@student.itera.ac.id',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 14, // Division ID 14
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => true,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Bima Dharma',
                'last_name' => 'Yahya',
                'email' => 'bimadharmayahya@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 14, // Division ID 14
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Reygan',
                'last_name' => 'Fadhilah',
                'email' => 'fadhilahreygan@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 14, // Division ID 14
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Vyrra',
                'last_name' => 'Fitriana',
                'email' => 'vyrra.hartanto@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 14, // Division ID 14
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Muhammad',
                'last_name' => 'Fauzan',
                'email' => 'm.fauzan.hb@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 14, // Division ID 14
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // David Developer
            [
                'first_name' => 'David',
                'last_name' => 'Dwiyanto',
                'email' => 'daviddwiyanto.student@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 15, // Division ID 15
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => true,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => false, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Dimas Deo',
                'last_name' => 'Rezkidyo',
                'email' => 'dimasrdyo@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 15, // Division ID 15
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => true,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => false, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Ahmad Sofi',
                'last_name' => 'Sidik',
                'email' => 'sofi.sidik12@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 15, // Division ID 15
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Rengga Rendi',
                'last_name' => 'Saputra',
                'email' => 'renggarendy22@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 15, // Division ID 15
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // Quality Assurance
            [
                'first_name' => 'Falah',
                'last_name' => 'Shalahuddin',
                'email' => 'falahshalahuddin@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 16, // Division ID 16
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => true,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => false, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Nadindra Maulana',
                'last_name' => 'Aziz',
                'email' => 'maulananadindra@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 16, // Division ID 16
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Ravita Nurul',
                'last_name' => 'Asmi',
                'email' => 'ravitanrl@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 16, // Division ID 16
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // DevOps
            [
                'first_name' => 'Della',
                'last_name' => 'Setyowati',
                'email' => 'dellastywt5@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 17, // Division ID 17
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => true,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => false, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'first_name' => 'Ananda Andreas',
                'last_name' => 'Dharma',
                'email' => 'anandaandreas11@gmail.com',
                'instagram' => '-',
                'linkedin' => '-',
                'batch_no' => null,
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
                'c_level_id' => null, // Not a C-Level
                'division_id' => 17, // Division ID 17
                'supervisor_id' => null,
                'vice_supervisor_id' => null,
                'HFlag' => false,
                'ChFlag' => false,
                'CFlag' => false,
                'Sflag' => false,
                'StFlag' => true, // StFlag set to true
                'profile_picture' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        DB::table('users')->insert($users);
    }
}