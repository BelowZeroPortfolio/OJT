<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Determine if the user can view any attendance records.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Admins can view all attendance records
        return $user->isAdmin();
    }

    /**
     * Determine if the user can view the attendance record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AttendanceRecord  $attendanceRecord
     * @return bool
     */
    public function view(User $user, AttendanceRecord $attendanceRecord): bool
    {
        // Admins can view any attendance record
        if ($user->isAdmin()) {
            return true;
        }
        
        // Students can only view their own attendance records
        return $user->id === $attendanceRecord->user_id;
    }

    /**
     * Determine if the user can create attendance records.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Only admins can manually create attendance records
        // RFID scanners create records through a separate mechanism
        return $user->isAdmin();
    }

    /**
     * Determine if the user can update the attendance record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AttendanceRecord  $attendanceRecord
     * @return bool
     */
    public function update(User $user, AttendanceRecord $attendanceRecord): bool
    {
        // Only admins can update attendance records
        return $user->isAdmin();
    }

    /**
     * Determine if the user can delete the attendance record.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AttendanceRecord  $attendanceRecord
     * @return bool
     */
    public function delete(User $user, AttendanceRecord $attendanceRecord): bool
    {
        // Only admins can delete attendance records
        return $user->isAdmin();
    }
}
