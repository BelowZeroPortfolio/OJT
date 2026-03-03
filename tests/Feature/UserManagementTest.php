<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_students_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $students = User::factory()->count(3)->create(['role' => 'student']);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertViewHas('students');
    }

    public function test_admin_can_create_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $location = Location::factory()->create();

        $studentData = [
            'name' => 'John Doe',
            'student_id' => '2024-001',
            'email' => 'john@example.com',
            'password' => 'password123',
            'course' => 'Computer Science',
            'assigned_location_id' => $location->id,
        ];

        $response = $this->actingAs($admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('admin.users.store'), $studentData);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'student_id' => '2024-001',
            'email' => 'john@example.com',
            'role' => 'student',
        ]);
    }

    public function test_admin_can_update_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $location = Location::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'student_id' => $student->student_id,
            'email' => $student->email,
            'course' => 'Updated Course',
            'assigned_location_id' => $location->id,
        ];

        $response = $this->actingAs($admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->put(route('admin.users.update', $student), $updateData);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'name' => 'Updated Name',
            'course' => 'Updated Course',
        ]);
    }

    public function test_admin_can_delete_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('admin.users.destroy', $student));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('users', [
            'id' => $student->id,
        ]);
    }

    public function test_student_creation_requires_all_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('admin.users.store'), []);

        $response->assertSessionHasErrors(['name', 'student_id', 'email', 'password', 'course', 'assigned_location_id']);
    }

    public function test_student_id_must_be_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $existingStudent = User::factory()->create(['role' => 'student', 'student_id' => '2024-001']);
        $location = Location::factory()->create();

        $response = $this->actingAs($admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('admin.users.store'), [
                'name' => 'John Doe',
                'student_id' => '2024-001',
                'email' => 'john@example.com',
                'password' => 'password123',
                'course' => 'Computer Science',
                'assigned_location_id' => $location->id,
            ]);

        $response->assertSessionHasErrors(['student_id']);
    }
}
