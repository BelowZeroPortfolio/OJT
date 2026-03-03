<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active locations
        $locations = Location::where('is_active', true)->pluck('id')->toArray();

        if (empty($locations)) {
            $this->command->warn('No active locations found. Please run LocationSeeder first.');
            return;
        }

        // Generate 50 student accounts (lower end of 50-100 range for faster seeding)
        $students = [];
        $password = Hash::make('password');
        $courses = [
            'Computer Science',
            'Information Technology',
            'Business Administration',
            'Accounting',
            'Engineering',
            'Nursing',
        ];

        for ($i = 1; $i <= 50; $i++) {
            $students[] = [
                'student_id' => sprintf('%04d-%03d', 2024, $i),
                'name' => 'Student ' . $i,
                'email' => 'student' . $i . '@ojt.edu',
                'password' => $password,
                'role' => 'student',
                'course' => $courses[array_rand($courses)],
                'assigned_location_id' => $locations[array_rand($locations)],
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert in chunks for better performance
        foreach (array_chunk($students, 25) as $chunk) {
            User::insert($chunk);
        }

        $this->command->info('Created 50 student accounts with location assignments.');
    }
}
