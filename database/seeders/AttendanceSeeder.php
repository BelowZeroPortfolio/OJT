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
        $attendanceMethods = ['rfid', 'qr_code'];

        // Generate attendance records for the past 14 days
        foreach ($students as $student) {
            // Each student attends 8-12 days out of the past 14 days
            $daysToAttend = rand(8, 12);
            $attendedDates = [];

            // Generate attendance for random days, skipping weekends
            $attempts = 0;
            while (count($attendedDates) < $daysToAttend && $attempts < 30) {
                $daysAgo = rand(1, 14);
                $date = Carbon::now()->subDays($daysAgo)->startOfDay();
                
                // Skip weekends and already selected dates
                if (!$date->isWeekend() && !isset($attendedDates[$date->format('Y-m-d')])) {
                    $attendedDates[$date->format('Y-m-d')] = $date;
                }
                $attempts++;
            }

            foreach ($attendedDates as $date) {
                // Check in: between 7:00 AM and 9:00 AM
                $checkIn = (clone $date)->setTime(
                    rand(7, 8),
                    rand(0, 59),
                    rand(0, 59)
                );

                // 90% chance of having check out (complete record)
                $hasCheckOut = rand(1, 100) <= 90;
                $checkOut = null;
                $totalHours = null;

                if ($hasCheckOut) {
                    // Check out: 6-9 hours after check in
                    $hoursWorked = rand(6, 9);
                    $checkOut = (clone $checkIn)->addHours($hoursWorked)->addMinutes(rand(0, 59));
                    $totalHours = round($checkOut->diffInMinutes($checkIn) / 60, 2);
                }

                $method = $attendanceMethods[array_rand($attendanceMethods)];

                $allRecords[] = [
                    'user_id' => $student->id,
                    'location_id' => $student->assigned_location_id,
                    'date' => $date->format('Y-m-d'),
                    'time_in' => $checkIn,
                    'time_out' => $checkOut,
                    'total_hours' => $totalHours,
                    'scan_type' => 'time_in',
                    'scan_method' => $method,
                    'scanner_ip' => '192.168.1.' . rand(100, 200),
                    'is_valid' => true,
                    'created_at' => $checkIn,
                    'updated_at' => $checkOut ?? $checkIn,
                ];
            }
        }

        // Insert all records
        foreach (array_chunk($allRecords, 100) as $chunk) {
            AttendanceRecord::insert($chunk);
        }

        $recordsCreated = count($allRecords);
        $this->command->info("Created {$recordsCreated} attendance records for {$students->count()} students over the past 14 days.");
    }
}
