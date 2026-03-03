<?php

namespace App\Services;

use App\Models\User;
use App\Models\Location;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Validate that a student exists in the database.
     *
     * @param string $studentId
     * @return User|null
     */
    public function validateStudentExists(string $studentId): ?User
    {
        return User::where('student_id', $studentId)
            ->where('role', 'student')
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Validate that the scanned location matches the student's assigned location.
     *
     * @param User $student
     * @param int $locationId
     * @return bool
     */
    public function validateLocationMatch(User $student, int $locationId): bool
    {
        return $student->assigned_location_id === $locationId;
    }

    /**
     * Check for duplicate entry on the same day at the same location.
     *
     * @param int $userId
     * @param int $locationId
     * @param string $date
     * @param string $scanType
     * @return bool
     */
    public function checkDuplicateEntry(int $userId, int $locationId, string $date, string $scanType): bool
    {
        return AttendanceRecord::where('user_id', $userId)
            ->where('location_id', $locationId)
            ->where('date', $date)
            ->where('scan_type', $scanType)
            ->exists();
    }

    /**
     * Create an attendance record.
     *
     * @param array $data
     * @return AttendanceRecord
     */
    public function createAttendanceRecord(array $data): AttendanceRecord
    {
        $timestamp = Carbon::parse($data['timestamp']);
        
        $attendanceData = [
            'user_id' => $data['user_id'],
            'location_id' => $data['location_id'],
            'date' => $timestamp->toDateString(),
            'scan_type' => $data['scan_type'],
            'scanner_ip' => $data['scanner_ip'] ?? null,
            'is_valid' => $data['is_valid'] ?? true,
            'validation_notes' => $data['validation_notes'] ?? null,
        ];

        // Set time_in or time_out based on scan_type
        if ($data['scan_type'] === 'time_in') {
            $attendanceData['time_in'] = $timestamp;
        } else {
            $attendanceData['time_out'] = $timestamp;
            
            // If this is a time_out, find the corresponding time_in record and update it
            $timeInRecord = AttendanceRecord::where('user_id', $data['user_id'])
                ->where('location_id', $data['location_id'])
                ->where('date', $timestamp->toDateString())
                ->where('scan_type', 'time_in')
                ->first();

            if ($timeInRecord) {
                $timeInRecord->time_out = $timestamp;
                $timeInRecord->total_hours = $this->calculateTotalHours($timeInRecord->time_in, $timestamp);
                $timeInRecord->save();
                
                // Invalidate caches after updating attendance record
                $this->cacheService->invalidateOnAttendanceCreation($data['user_id']);
                
                // Return the updated record instead of creating a new one
                return $timeInRecord;
            }
            
            // If no time_in record exists, still create the time_out record
            $attendanceData['time_in'] = $timestamp; // Set time_in to same as time_out for standalone time_out
        }

        $record = AttendanceRecord::create($attendanceData);
        
        // Invalidate caches after creating attendance record
        $this->cacheService->invalidateOnAttendanceCreation($data['user_id']);
        
        return $record;
    }

    /**
     * Calculate total hours between time-in and time-out.
     *
     * @param string|\Carbon\Carbon $timeIn
     * @param string|\Carbon\Carbon $timeOut
     * @return float
     */
    public function calculateTotalHours($timeIn, $timeOut): float
    {
        $timeInCarbon = $timeIn instanceof Carbon ? $timeIn : Carbon::parse($timeIn);
        $timeOutCarbon = $timeOut instanceof Carbon ? $timeOut : Carbon::parse($timeOut);
        
        return round($timeOutCarbon->diffInMinutes($timeInCarbon) / 60, 2);
    }
}
