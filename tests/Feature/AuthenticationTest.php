<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
            ->assertViewIs('auth.login')
            ->assertSee('Sign in to your account')
            ->assertSee('Email address')
            ->assertSee('Password');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'token',
                'user' => ['id', 'name', 'email', 'role'],
            ]);
    }

    public function test_web_login_redirects_based_on_role(): void
    {
        $student = User::factory()->create([
            'email' => 'student@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
        ]);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/login', [
                'email' => 'student@example.com',
                'password' => 'password123',
            ]);

        $response->assertRedirect('/student/dashboard');

        // Test admin redirect
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/logout');
        
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/login', [
                'email' => 'admin@example.com',
                'password' => 'password123',
            ]);

        $response->assertRedirect('/admin/dashboard');
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout successful',
            ]);
    }

    public function test_role_middleware_restricts_student_access(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $token = $student->createToken('test-token')->plainTextToken;

        // Create a test route that requires admin role
        \Illuminate\Support\Facades\Route::get('/test-admin-only', function () {
            return response()->json(['message' => 'Admin access']);
        })->middleware(['auth:sanctum', 'role:admin']);

        $response = $this->getJson('/test-admin-only', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Access denied',
            ]);
    }

    public function test_role_middleware_allows_admin_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        // Create a test route that requires admin role
        \Illuminate\Support\Facades\Route::get('/test-admin-access', function () {
            return response()->json(['message' => 'Admin access']);
        })->middleware(['auth:sanctum', 'role:admin']);

        $response = $this->getJson('/test-admin-access', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Admin access',
            ]);
    }
}
