<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_locations_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $locations = Location::factory()->count(3)->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.locations.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.locations.index');
        $response->assertViewHas('locations');
    }

    public function test_admin_can_create_location(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $locationData = [
            'location_code' => 'LOC-001',
            'name' => 'Main Office',
            'address' => '123 Main St',
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('admin.locations.store'), $locationData);

        $response->assertRedirect(route('admin.locations.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('locations', [
            'location_code' => 'LOC-001',
            'name' => 'Main Office',
        ]);
    }

    public function test_admin_can_update_location(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $location = Location::factory()->create();

        $updateData = [
            'location_code' => $location->location_code,
            'name' => 'Updated Location Name',
            'address' => 'Updated Address',
            'is_active' => false,
        ];

        $response = $this->actingAs($admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->put(route('admin.locations.update', $location), $updateData);

        $response->assertRedirect(route('admin.locations.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => 'Updated Location Name',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_location(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $location = Location::factory()->create();

        $response = $this->actingAs($admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('admin.locations.destroy', $location));

        $response->assertRedirect(route('admin.locations.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('locations', [
            'id' => $location->id,
        ]);
    }

    public function test_location_creation_requires_required_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('admin.locations.store'), []);

        $response->assertSessionHasErrors(['location_code', 'name']);
    }

    public function test_location_code_must_be_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $existingLocation = Location::factory()->create(['location_code' => 'LOC-001']);

        $response = $this->actingAs($admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('admin.locations.store'), [
                'location_code' => 'LOC-001',
                'name' => 'Another Location',
            ]);

        $response->assertSessionHasErrors(['location_code']);
    }
}
