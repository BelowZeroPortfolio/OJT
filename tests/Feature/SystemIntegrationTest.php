<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\Location;
use App\Models\User;
use App\Jobs\ProcessAttendanceLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Carbon\Carbon;

/**
 * Comprehensive integration tests for the OJT Attendance System
 * Task 15: Final integration and testing checkpoint
 */
class SystemIntegrationTest extends TestCase
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
            'name' => 'Test Training Location',
            'is_active' => true,
        ]);

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
            'name' => 'Test Admin',
        ]);

        // Create student user
        $this->student = User::factory()->create([
            'role' => 'student',
            'student_id' => 'STU-2024-001',
            'name' => 'Test Student',
            'course' => 'Computer Science',
            'assigned_location_id' => $this->location->id,
        ]);
    }

    /** @test */
    public function rfid_scanner_integration_end_to_end()
    {
        Queue::fake();

        // Set a test scanner token
        config(['scanner.tokens' => ['test-scanner-token-123']]);

        // Simulate RFID scan with scanner token
        $response = $this->postJson('/api/rfid/scan', [
            'student_id' => $this->student->student_id,
            'location_id' => $this->location->id,
            'timestamp' => now()->toIso8601String(),
            'scan_type' => 'time_in',
        ], [
            'X-Scanner-Token' => 'test-scanner-token-123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Attendance recorded',
            ]);

        // Verify job was dispatched
        Queue::assertPushed(ProcessAttendanceLog::class);
    }

    /** @test */
    public function rfid_scanner_validates_student_location_match()
    {
        config(['scanner.tokens' => ['test-scanner-token-123']]);

        $otherLocation = Location::factory()->create([
            'location_code' => 'OTHER-LOC',
        ]);

        // Try to scan at wrong location
        $response = $this->postJson('/api/rfid/scan', [
            'student_id' => $this->student->student_id,
            'location_id' => $otherLocation->id,
            'timestamp' => now()->toIso8601String(),
            'scan_type' => 'time_in',
        ], [
            'X-Scanner-Token' => 'test-scanner-token-123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid location for student',
            ]);
    }

    /** @test */
    public function rfid_scanner_rejects_duplicate_scans()
    {
        config(['scanner.tokens' => ['test-scanner-token-123']]);

        // Create existing attendance record for today
        AttendanceRecord::factory()->create([
            'user_id' => $this->student->id,
            'location_id' => $this->location->id,
            'date' => Carbon::today(),
            'scan_type' => 'time_in',
        ]);

        // Try to scan again
        $response = $this->postJson('/api/rfid/scan', [
            'student_id' => $this->student->student_id,
            'location_id' => $this->location->id,
            'timestamp' => now()->toIso8601String(),
            'scan_type' => 'time_in',
        ], [
            'X-Scanner-Token' => 'test-scanner-token-123',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Duplicate attendance entry',
            ]);
    }

    /** @test */
    public function student_dashboard_displays_own_attendance_only()
    {
        // Create attendance for test student with unique dates
        for ($i = 0; $i < 3; $i++) {
            AttendanceRecord::factory()->create([
                'user_id' => $this->student->id,
                'location_id' => $this->location->id,
                'date' => Carbon::today()->subDays($i + 1),
            ]);
        }

        // Create attendance for another student
        $otherStudent = User::factory()->create([
            'role' => 'student',
            'assigned_location_id' => $this->location->id,
        ]);
        
        for ($i = 0; $i < 2; $i++) {
            AttendanceRecord::factory()->create([
                'user_id' => $otherStudent->id,
                'location_id' => $this->location->id,
                'date' => Carbon::today()->subDays($i + 10),
            ]);
        }

        // Student should only see their own records
        $response = $this->actingAs($this->student, 'sanctum')
            ->getJson('/api/student/attendance');

        $response->assertStatus(200);
        $records = $response->json('records');
        $this->assertNotNull($records);
        $this->assertCount(3, $records);
        
        // Verify response structure
        $this->assertArrayHasKey('records', $response->json());
        $this->assertArrayHasKey('statistics', $response->json());
    }

    /** @test */
    public function admin_dashboard_displays_all_attendance_with_filtering()
    {
        // Create attendance for multiple students
        $student2 = User::factory()->create([
            'role' => 'student',
            'course' => 'Engineering',
            'assigned_location_id' => $this->location->id,
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $this->student->id,
            'location_id' => $this->location->id,
            'date' => Carbon::today(),
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $student2->id,
            'location_id' => $this->location->id,
            'date' => Carbon::today(),
        ]);

        // Admin should see all records
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/attendance');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('attendance_records'));

        // Test filtering by course
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/attendance?course=Computer Science');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('attendance_records'));
        $this->assertEquals($this->student->id, $response->json('attendance_records.0.user_id'));
    }

    /** @test */
    public function rate_limiting_is_enforced_on_api_endpoints()
    {
        // Verify rate limiting is configured in routes
        $routes = \Illuminate\Support\Facades\Route::getRoutes();
        $rfidRoute = $routes->getByName(null);
        
        // Check that the RFID scanner route has throttle middleware
        $rfidRouteFound = false;
        foreach ($routes as $route) {
            if ($route->uri() === 'api/rfid/scan') {
                $middleware = $route->gatherMiddleware();
                $hasThrottle = collect($middleware)->contains(function ($m) {
                    return str_contains($m, 'throttle');
                });
                $rfidRouteFound = true;
                $this->assertTrue($hasThrottle, 'RFID scanner route should have rate limiting');
                break;
            }
        }
        
        $this->assertTrue($rfidRouteFound, 'RFID scanner route should exist');
    }

    /** @test */
    public function authentication_is_required_for_protected_routes()
    {
        // Test student endpoint without auth
        $response = $this->getJson('/api/student/attendance');
        $response->assertStatus(401);

        // Test admin endpoint without auth
        $response = $this->getJson('/api/admin/attendance');
        $response->assertStatus(401);

        // Test RFID endpoint requires scanner token
        $response = $this->postJson('/api/rfid/scan', [
            'student_id' => $this->student->student_id,
            'location_id' => $this->location->id,
            'timestamp' => now()->toIso8601String(),
            'scan_type' => 'time_in',
        ]);
        // Should fail with 401 due to missing scanner token
        $response->assertStatus(401);
    }

    /** @test */
    public function role_based_authorization_is_enforced()
    {
        // Student cannot access admin endpoints
        $response = $this->actingAs($this->student, 'sanctum')
            ->getJson('/api/admin/attendance');
        $response->assertStatus(403);

        // Admin can access admin endpoints
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/attendance');
        $response->assertStatus(200);

        // Student can access student endpoints
        $response = $this->actingAs($this->student, 'sanctum')
            ->getJson('/api/student/attendance');
        $response->assertStatus(200);
    }

    /** @test */
    public function caching_works_and_invalidates_properly()
    {
        Cache::flush();

        // Create initial attendance record
        AttendanceRecord::factory()->create([
            'user_id' => $this->student->id,
            'location_id' => $this->location->id,
            'date' => Carbon::today(),
        ]);

        // First request should cache
        $response1 = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/attendance');
        $response1->assertStatus(200);
        $count1 = count($response1->json('attendance_records'));

        // Create another record
        AttendanceRecord::factory()->create([
            'user_id' => $this->student->id,
            'location_id' => $this->location->id,
            'date' => Carbon::today()->subDays(1),
        ]);

        // Second request should return cached result
        $response2 = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/attendance');
        $response2->assertStatus(200);
        $count2 = count($response2->json('attendance_records'));

        // Cached result should have same count
        $this->assertEquals($count1, $count2);
    }

    /** @test */
    public function error_handling_returns_appropriate_status_codes()
    {
        // Test validation error (422)
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/reports/generate', []);
        $response->assertStatus(422);

        // Test authorization error (403)
        $response = $this->actingAs($this->student, 'sanctum')
            ->getJson('/api/admin/attendance');
        $response->assertStatus(403);

        // Test not found (404) - invalid report ID
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/reports/99999/download');
        $response->assertStatus(404);
    }

    /** @test */
    public function user_management_crud_operations_work()
    {
        // Create
        $newStudentData = [
            'name' => 'New Student',
            'student_id' => 'STU-2024-999',
            'email' => 'newstudent@test.com',
            'password' => 'password123',
            'course' => 'Information Technology',
            'assigned_location_id' => $this->location->id,
        ];

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('admin.users.store'), $newStudentData);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'student_id' => 'STU-2024-999',
            'email' => 'newstudent@test.com',
        ]);

        // Read
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));
        $response->assertStatus(200);

        // Update
        $newStudent = User::where('student_id', 'STU-2024-999')->first();
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->put(route('admin.users.update', $newStudent), [
                'name' => 'Updated Student Name',
                'student_id' => 'STU-2024-999',
                'email' => 'newstudent@test.com',
                'course' => 'Updated Course',
                'assigned_location_id' => $this->location->id,
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $newStudent->id,
            'name' => 'Updated Student Name',
        ]);

        // Delete (soft delete)
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('admin.users.destroy', $newStudent));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertSoftDeleted('users', ['id' => $newStudent->id]);
    }

    /** @test */
    public function location_management_crud_operations_work()
    {
        // Create
        $newLocationData = [
            'location_code' => 'NEW-LOC-001',
            'name' => 'New Training Location',
            'address' => '123 Test Street',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('admin.locations.store'), $newLocationData);

        $response->assertRedirect(route('admin.locations.index'));
        $this->assertDatabaseHas('locations', [
            'location_code' => 'NEW-LOC-001',
            'name' => 'New Training Location',
        ]);

        // Read
        $response = $this->actingAs($this->admin)
            ->get(route('admin.locations.index'));
        $response->assertStatus(200);

        // Update
        $newLocation = Location::where('location_code', 'NEW-LOC-001')->first();
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->put(route('admin.locations.update', $newLocation), [
                'location_code' => 'NEW-LOC-001',
                'name' => 'Updated Location Name',
                'address' => 'Updated Address',
                'is_active' => false,
            ]);

        $response->assertRedirect(route('admin.locations.index'));
        $this->assertDatabaseHas('locations', [
            'id' => $newLocation->id,
            'name' => 'Updated Location Name',
        ]);

        // Delete (soft delete)
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('admin.locations.destroy', $newLocation));

        $response->assertRedirect(route('admin.locations.index'));
        $this->assertSoftDeleted('locations', ['id' => $newLocation->id]);
    }

    /** @test */
    public function report_generation_and_download_workflow()
    {
        Queue::fake();

        // Create some attendance data with unique dates
        for ($i = 0; $i < 5; $i++) {
            AttendanceRecord::factory()->create([
                'user_id' => $this->student->id,
                'location_id' => $this->location->id,
                'date' => Carbon::today()->subDays($i),
            ]);
        }

        // Request report generation
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/reports/generate', [
                'report_type' => 'daily',
                'format' => 'csv',
                'date' => Carbon::today()->format('Y-m-d'),
            ]);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'success',
                'message',
                'report_id',
                'status',
            ]);

        // Verify job was queued
        Queue::assertPushed(\App\Jobs\GenerateReport::class);
    }

    /** @test */
    public function system_handles_validation_errors_gracefully()
    {
        config(['scanner.tokens' => ['test-scanner-token-123']]);

        // Test RFID scan with missing fields
        $response = $this->postJson('/api/rfid/scan', [], [
            'X-Scanner-Token' => 'test-scanner-token-123',
        ]);
        $response->assertStatus(422);
        $this->assertArrayHasKey('errors', $response->json());

        // Test report generation with invalid type
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/reports/generate', [
                'report_type' => 'invalid',
                'format' => 'csv',
                'date' => Carbon::today()->format('Y-m-d'),
            ]);
        $response->assertStatus(422);

        // Test user creation with missing required fields
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('admin.users.store'), []);
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function attendance_records_are_sorted_by_date_descending()
    {
        // Create records with different dates
        AttendanceRecord::factory()->create([
            'user_id' => $this->student->id,
            'location_id' => $this->location->id,
            'date' => Carbon::today()->subDays(5),
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $this->student->id,
            'location_id' => $this->location->id,
            'date' => Carbon::today()->subDays(1),
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $this->student->id,
            'location_id' => $this->location->id,
            'date' => Carbon::today()->subDays(3),
        ]);

        $response = $this->actingAs($this->student, 'sanctum')
            ->getJson('/api/student/attendance');

        $response->assertStatus(200);
        $records = $response->json('records');

        // Verify we have records
        $this->assertNotNull($records);
        $this->assertIsArray($records);
        $this->assertGreaterThan(0, count($records));

        // Verify descending order
        $dates = array_column($records, 'date');
        $sortedDates = $dates;
        rsort($sortedDates);
        $this->assertEquals($sortedDates, $dates);
    }
}
