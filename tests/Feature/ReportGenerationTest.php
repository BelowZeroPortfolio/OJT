<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Location;
use App\Models\AttendanceRecord;
use App\Models\Report;
use App\Jobs\GenerateReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Carbon\Carbon;

class ReportGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /** @test */
    public function admin_can_request_report_generation()
    {
        Queue::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson('/api/admin/reports/generate', [
            'report_type' => 'daily',
            'format' => 'csv',
            'date' => '2026-03-02',
        ]);

        $response->assertStatus(202)
            ->assertJson([
                'success' => true,
                'message' => 'Report generation started',
            ])
            ->assertJsonStructure([
                'report_id',
                'status',
            ]);

        Queue::assertPushed(GenerateReport::class);
    }

    /** @test */
    public function student_cannot_request_report_generation()
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student, 'sanctum');

        $response = $this->postJson('/api/admin/reports/generate', [
            'report_type' => 'daily',
            'format' => 'csv',
            'date' => '2026-03-02',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function report_generation_validates_required_fields()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson('/api/admin/reports/generate', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['report_type', 'format', 'date']);
    }

    /** @test */
    public function report_generation_validates_report_type()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson('/api/admin/reports/generate', [
            'report_type' => 'invalid',
            'format' => 'csv',
            'date' => '2026-03-02',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['report_type']);
    }

    /** @test */
    public function report_generation_validates_format()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson('/api/admin/reports/generate', [
            'report_type' => 'daily',
            'format' => 'invalid',
            'date' => '2026-03-02',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['format']);
    }

    /** @test */
    public function admin_can_check_report_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $report = Report::factory()->create([
            'generated_by' => $admin->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson("/api/admin/reports/{$report->id}/status");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'report_id' => $report->id,
                'status' => 'pending',
            ]);
    }

    /** @test */
    public function admin_cannot_download_pending_report()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $report = Report::factory()->create([
            'generated_by' => $admin->id,
            'status' => 'pending',
            'file_path' => null,
        ]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson("/api/admin/reports/{$report->id}/download");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Report is not ready yet',
            ]);
    }

    /** @test */
    public function admin_can_download_completed_report()
    {
        Storage::fake('local');
        Storage::put('reports/test_report.csv', 'test content');

        $admin = User::factory()->create(['role' => 'admin']);
        $report = Report::factory()->create([
            'generated_by' => $admin->id,
            'status' => 'completed',
            'file_path' => 'reports/test_report.csv',
        ]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->getJson("/api/admin/reports/{$report->id}/download");

        $response->assertStatus(200);
    }

    /** @test */
    public function report_service_generates_daily_report()
    {
        $location = Location::factory()->create();
        $student = User::factory()->create([
            'role' => 'student',
            'assigned_location_id' => $location->id,
        ]);

        // Create attendance records
        AttendanceRecord::factory()->create([
            'user_id' => $student->id,
            'location_id' => $location->id,
            'date' => '2026-03-02',
            'time_in' => '2026-03-02 08:00:00',
            'time_out' => '2026-03-02 17:00:00',
            'total_hours' => 9.0,
            'scan_type' => 'time_in',
        ]);

        $reportService = app(\App\Services\ReportService::class);
        $data = $reportService->generateDailyReport('2026-03-02');

        $this->assertNotEmpty($data);
        $this->assertEquals($student->name, $data->first()['student_name']);
        $this->assertEquals(9.0, $data->first()['total_hours']);
    }

    /** @test */
    public function report_service_applies_filters()
    {
        $location1 = Location::factory()->create();
        $location2 = Location::factory()->create();
        
        $student1 = User::factory()->create([
            'role' => 'student',
            'course' => 'Computer Science',
            'assigned_location_id' => $location1->id,
        ]);
        
        $student2 = User::factory()->create([
            'role' => 'student',
            'course' => 'Information Technology',
            'assigned_location_id' => $location2->id,
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $student1->id,
            'location_id' => $location1->id,
            'date' => '2026-03-02',
            'scan_type' => 'time_in',
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $student2->id,
            'location_id' => $location2->id,
            'date' => '2026-03-02',
            'scan_type' => 'time_in',
        ]);

        $reportService = app(\App\Services\ReportService::class);
        
        // Filter by course
        $data = $reportService->generateDailyReport('2026-03-02', [
            'course' => 'Computer Science',
        ]);

        $this->assertCount(1, $data);
        $this->assertEquals($student1->name, $data->first()['student_name']);
    }

    /** @test */
    public function report_service_exports_to_csv()
    {
        Storage::fake('local');

        $data = collect([
            [
                'student_name' => 'John Doe',
                'course' => 'Computer Science',
                'total_hours' => 40.0,
                'attendance_percentage' => 100.0,
            ],
        ]);

        $reportService = app(\App\Services\ReportService::class);
        $filePath = $reportService->exportToCsv($data, 'test_report');

        Storage::assertExists($filePath);
        $content = Storage::get($filePath);
        $this->assertStringContainsString('John Doe', $content);
        $this->assertStringContainsString('Computer Science', $content);
    }
}
