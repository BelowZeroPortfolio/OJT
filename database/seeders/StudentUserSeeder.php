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

        // Generate 10 student accounts
        $students = [];
        $password = Hash::make('password');
        $courses = [
            'Computer Science',
            'Information Technology',
            'Business Administration',
            'Accounting',
            'Engineering',
        ];

        $studentNames = [
            'Juan Dela Cruz',
            'Maria Clara',
            'Jose Rizal',
            'Andres Bonifacio',
            'Emilio Aguinaldo',
            'Gabriela Silang',
            'Apolinario Mabini',
            'Melchora Aquino',
            'Diego Silang',
            'Marcelo Del Pilar',
        ];

        for ($i = 1; $i <= 10; $i++) {
            $students[] = [
                'student_id' => sprintf('2024-%03d', $i),
                'name' => $studentNames[$i - 1],
                'email' => 'student' . $i . '@ojt.edu',
                'password' => $password,
                'role' => 'student',
                'course' => $courses[($i - 1) % count($courses)],
                'assigned_location_id' => $locations[($i - 1) % count($locations)], // Distribute evenly
                'rfid_number' => sprintf('RFID%06d', $i),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        User::insert($students);

        $this->command->info('Created 10 student accounts with location assignments and RFID numbers.');
    }
}
