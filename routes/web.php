<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ReportsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Student dashboard route
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    Route::post('/student/rfid-attendance', [\App\Http\Controllers\Api\StudentAttendanceController::class, 'rfidAttendance'])->name('student.rfid-attendance');
    Route::get('/student/check-updates', [StudentDashboardController::class, 'checkUpdates'])->name('student.check-updates');
});

// Admin dashboard route
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // Reports and Activity Logs
    Route::get('/admin/reports', [ReportsController::class, 'index'])->name('admin.reports');
    
    Route::get('/admin/activity-logs', function () {
        return view('admin.activity-logs');
    })->name('admin.activity-logs');
    
    // RFID Registration routes
    Route::get('/admin/rfid-registration', [\App\Http\Controllers\Admin\RfidRegistrationController::class, 'index'])->name('admin.rfid-registration');
    Route::post('/admin/rfid-registration/register', [\App\Http\Controllers\Admin\RfidRegistrationController::class, 'register']);
    Route::post('/admin/rfid-registration/unregister', [\App\Http\Controllers\Admin\RfidRegistrationController::class, 'unregister']);
    
    // User management routes
    Route::resource('admin/users', \App\Http\Controllers\UserController::class)
        ->names([
            'index' => 'admin.users.index',
            'create' => 'admin.users.create',
            'store' => 'admin.users.store',
            'edit' => 'admin.users.edit',
            'update' => 'admin.users.update',
            'destroy' => 'admin.users.destroy',
        ])
        ->except(['show']);
    
    // Location management routes
    Route::resource('admin/locations', \App\Http\Controllers\LocationController::class)
        ->names([
            'index' => 'admin.locations.index',
            'create' => 'admin.locations.create',
            'store' => 'admin.locations.store',
            'edit' => 'admin.locations.edit',
            'update' => 'admin.locations.update',
            'destroy' => 'admin.locations.destroy',
        ])
        ->except(['show']);
});

// Test Supabase connection route
Route::get('/test-supabase', function () {
    try {
        // Test database connection
        $connection = config('database.default');
        $host = config('database.connections.' . $connection . '.host');
        
        if (str_contains($host, 'supabase.co')) {
            // Test if we can connect to Supabase
            DB::select('SELECT 1 as test');
            
            return response()->json([
                'status' => 'success',
                'message' => 'Supabase connection successful',
                'connection' => $connection,
                'host' => $host,
                'realtime_enabled' => true
            ]);
        } else {
            return response()->json([
                'status' => 'info',
                'message' => 'Using local database (not Supabase)',
                'connection' => $connection,
                'host' => $host,
                'realtime_enabled' => false
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database connection failed: ' . $e->getMessage(),
            'realtime_enabled' => false
        ], 500);
    }
})->name('test.supabase');
