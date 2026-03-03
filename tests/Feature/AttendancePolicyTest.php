<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendancePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_any_attendance_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($admin->can('viewAny', AttendanceRecord::class));
    }

    public function test_student_cannot_view_any_attendance_records(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->assertFalse($student->can('viewAny', AttendanceRecord::class));
    }

    public function test_student_can_view_own_attendance_record(): void
    {
        $location = Location::factory()->create();
        $student = User::factory()->create([
            'role' => 'student',
            'assigned_location_id' => $location->id,
        ]);

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $student->id,
            'location_id' => $location->id,
        ]);

        $this->assertTrue($student->can('view', $attendanceRecord));
    }

    public function test_student_cannot_view_other_student_attendance_record(): void
    {
        $location = Location::factory()->create();
        $student1 = User::factory()->create([
            'role' => 'student',
            'assigned_location_id' => $location->id,
        ]);
        $student2 = User::factory()->create([
            'role' => 'student',
            'assigned_location_id' => $location->id,
        ]);

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $student2->id,
            'location_id' => $location->id,
        ]);

        $this->assertFalse($student1->can('view', $attendanceRecord));
    }

    public function test_admin_can_view_any_student_attendance_record(): void
    {
        $location = Location::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'student',
            'assigned_location_id' => $location->id,
        ]);

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $student->id,
            'location_id' => $location->id,
        ]);

        $this->assertTrue($admin->can('view', $attendanceRecord));
    }

    public function test_only_admin_can_update_attendance_records(): void
    {
        $location = Location::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'student',
            'assigned_location_id' => $location->id,
        ]);

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $student->id,
            'location_id' => $location->id,
        ]);

        $this->assertTrue($admin->can('update', $attendanceRecord));
        $this->assertFalse($student->can('update', $attendanceRecord));
    }

    public function test_only_admin_can_delete_attendance_records(): void
    {
        $location = Location::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'student',
            'assigned_location_id' => $location->id,
        ]);

        $attendanceRecord = AttendanceRecord::factory()->create([
            'user_id' => $student->id,
            'location_id' => $location->id,
        ]);

        $this->assertTrue($admin->can('delete', $attendanceRecord));
        $this->assertFalse($student->can('delete', $attendanceRecord));
    }
}
