<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 3 admin accounts with known credentials
        $admins = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@ojt.edu',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'student_id' => null,
                'course' => null,
                'assigned_location_id' => null,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'OJT Coordinator',
                'email' => 'coordinator@ojt.edu',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'student_id' => null,
                'course' => null,
                'assigned_location_id' => null,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Department Head',
                'email' => 'head@ojt.edu',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'student_id' => null,
                'course' => null,
                'assigned_location_id' => null,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($admins as $admin) {
            User::create($admin);
        }
    }
}
