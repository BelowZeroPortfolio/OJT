<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all students with assigned locations
        $students = User::where('role', 'student')
            ->whereNotNull('assigned_location_id')
            ->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students with assigned locations found. Please run StudentUserSeeder first.');
            return;
        }

        $allRecords = [];

        // Generate attendance records for the past 30 days
        foreach ($students as $student) {
            // Each student attends 15-25 days out of the past 30 days (realistic pattern)
            $daysToAttend = rand(15, 25);
            $attendedDates = [];

            // Generate attendance for random days, skipping weekends
            $attempts = 0;
            while (count($attendedDates) < $daysToAttend && $attempts < 50) {
                $daysAgo = rand(1, 30); // Start from 1 to avoid today
                $date = Carbon::now()->subDays($daysAgo)->startOfDay();
                
                // Skip weekends and already selected dates
                if (!$date->isWeekend() && !isset($attendedDates[$date->format('Y-m-d')])) {
                    $attendedDates[$date->format('Y-m-d')] = $date;
                }
                $attempts++;
            }

            foreach ($attendedDates as $date) {
                // Time in: between 7:00 AM and 9:00 AM
                $timeIn = (clone $date)->setTime(
                    rand(7, 8),
                    rand(0, 59),
                    rand(0, 59)
                );

                // 85% chance of having time_out (complete record)
                $hasTimeOut = rand(1, 100) <= 85;
                $timeOut = null;
                $totalHours = null;

                if ($hasTimeOut) {
                    // Time out: 6-10 hours after time in
                    $hoursWorked = rand(6, 10);
                    $timeOut = (clone $timeIn)->addHours($hoursWorked)->addMinutes(rand(0, 59));
                    $totalHours = round($timeOut->diffInMinutes($timeIn) / 60, 2);
                }

                // 95% of records are valid
                $isValid = rand(1, 100) <= 95;

                $allRecords[] = [
                    'user_id' => $student->id,
                    'location_id' => $student->assigned_location_id,
                    'date' => $date->format('Y-m-d'),
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'total_hours' => $totalHours,
                    'scan_type' => 'time_in',
                    'scanner_ip' => '192.168.1.' . rand(1, 254),
                    'is_valid' => $isValid,
                    'validation_notes' => $isValid ? null : 'Location mismatch detected',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert all records in chunks for better performance
        $chunks = array_chunk($allRecords, 500);
        foreach ($chunks as $chunk) {
            AttendanceRecord::insert($chunk);
        }

        $recordsCreated = count($allRecords);
        $this->command->info("Created {$recordsCreated} attendance records for {$students->count()} students over the past 30 days.");
    }
}
