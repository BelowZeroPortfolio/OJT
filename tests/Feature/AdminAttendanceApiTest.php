<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Carbon\Carbon;

class AdminAttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;
    protected Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test location
        $this->location = Location::factory()->create([
            'location_code' => 'TEST-LOC-001',
            'name' => 'Test Location',
            'is_active' => true,
        ]);

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
        ]);

        // Create student user
        $this->student = User::factory()->create([
            'role' => 'student',
            'student_id' => 'STU-001',
            'name' => 'Test Student',
            'course' => 'Computer Science',
            'assigned_location_id' => $this->location->id,
        ]);
    }

    /** @test */
    public function admin_can_access_attendance_endpoint()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/attendance');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'attendance_records',
            'pagination' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
            'statistics' => [
                'total_students',
                'present_today',
                'absent_today',
            ],
        ]);
    }

    /** @test */
    public function student_cannot_access_admin_attendance_endpoint()
    {
        $response = $this->actingAs($this->student, 'sanctum')
            ->getJson('/api/admin/attendance');

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_admin_attendance_endpoint()
    {
        $response = $this->getJson('/api/admin/attendance');

        $response->assertStatus(401);
    }

    /** @test */
    public function endpoint_returns_all_attendance_records()
    {
        // Create attendance records with unique dates to avoid constraint violations
        for ($i = 0; $i < 5; $i++) {
            AttendanceRecord::factory()->create([
                'user_id' => $this->student->id,
                'location_id' => $this->location->id,
                'date' => Carbon::today()->subDays($i),
            ]);
        }

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/attendance');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('attendance_records'));
    }

    /** @test */
    public function endpoint_filters_by_student_name()
    {
        $student2 = User::factory()->create([
            'role' => 'student',
            'name' => 'Another Student',
            'assigned_location_id' => $this->location->id,
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $this->student->id,
            'location_id' => $this->location->id,
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $student2->id,
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/attendance?student_name=Test Student');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('attendance_records'));
        $this->assertEquals($this->student->id, $response->json('attendance_records.0.user_id'));
    }

    /** @test */
    public function endpoint_filters_by_course()
    {
        $student2 = User::factory()->create([
            'role' => 'student',
            'course' => 'Engineering',
            'assigned_location_id' => $this->location->id,
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $this->student->id,
            'location_id' => $this->location->id,
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $student2->id,
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/attendance?course=Computer Science');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('attendance_records'));
    }

    /** @test */
    public function endpoint_filters_by_location()
    {
        $location2 = Location::factory()->create([
            'location_code' => 'TEST-LOC-002',
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $this->student->id,
            'location_id' => $this->location->id,
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $this->student->id,
            'location_id' => $location2->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/attendance?location_id=' . $this->location->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('attendance_records'));
        $this->assertEquals($this->location->id, $response->json('attendance_records.0.location_id'));
    }

    /** @test */
    public function endpoint_filters_by_date_range()
    {
        AttendanceRecord::factory()->create([
            'user_id' => $this->student->id,
            'location_id' => $this->location->id,
            'date' => Carbon::today()->subDays(5),
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $this->student->id,
            'location_id' => $this->location->id,
            'date' => Carbon::today(),
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/attendance?date_from=' . Carbon::today()->format('Y-m-d'));

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('attendance_records'));
    }

    /** @test */
    public function endpoint_uses_caching()
    {
        Cache::flush();

        AttendanceRecord::factory()->create([
            'user_id' => $this->student->id,
            'location_id' => $this->location->id,
        ]);

        // First request should cache the result
        $response1 = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/attendance');

        $response1->assertStatus(200);

        // Create another record
        AttendanceRecord::factory()->create([
            'user_id' => $this->student->id,
            'location_id' => $this->location->id,
        ]);

        // Second request should return cached result (still 1 record)
        $response2 = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/attendance');

        $response2->assertStatus(200);
        $this->assertCount(1, $response2->json('attendance_records'));
    }

    /** @test */
    public function endpoint_returns_paginated_results()
    {
        // Create 25 attendance records with unique dates to avoid constraint violations
        for ($i = 0; $i < 25; $i++) {
            AttendanceRecord::factory()->create([
                'user_id' => $this->student->id,
                'location_id' => $this->location->id,
                'date' => Carbon::today()->subDays($i),
            ]);
        }

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/attendance');

        $response->assertStatus(200);
        $this->assertCount(20, $response->json('attendance_records'));
        $this->assertEquals(25, $response->json('pagination.total'));
        $this->assertEquals(2, $response->json('pagination.last_page'));
    }

    /** @test */
    public function endpoint_includes_statistics()
    {
        // Create multiple students
        $students = User::factory()->count(10)->create([
            'role' => 'student',
            'assigned_location_id' => $this->location->id,
        ]);

        // Create attendance for today for some students
        foreach ($students->take(5) as $index => $student) {
            AttendanceRecord::factory()->create([
                'user_id' => $student->id,
                'location_id' => $this->location->id,
                'date' => Carbon::today(),
                'scan_type' => 'time_in',
            ]);
        }

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/attendance');

        $response->assertStatus(200);
        $this->assertArrayHasKey('total_students', $response->json('statistics'));
        $this->assertArrayHasKey('present_today', $response->json('statistics'));
        $this->assertArrayHasKey('absent_today', $response->json('statistics'));
    }
}
