<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LocationSeeder::class,      // Create training locations first
            AdminUserSeeder::class,     // Create admin accounts
            StudentUserSeeder::class,   // Create student accounts with location assignments
            AttendanceSeeder::class,    // Create attendance records
        ]);
    }
}
