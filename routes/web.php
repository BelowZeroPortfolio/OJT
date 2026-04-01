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

// Gracefully handle accidental GET requests to /logout
Route::get('/logout', function () {
    return redirect()->route('login');
});

// Registration routes
Route::get('/register', [\App\Http\Controllers\RegistrationController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [\App\Http\Controllers\RegistrationController::class, 'register']);

// Student dashboard route
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    Route::get('/student/check-updates', [StudentDashboardController::class, 'checkUpdates'])->name('student.check-updates');
    
    // QR code scanner
    Route::get('/student/qr-scanner', function () {
        return view('student.qr-scanner');
    })->name('student.qr-scanner');
    
    // QR code scanning endpoint (web-based, using session auth)
    Route::post('/student/qr-scan', [\App\Http\Controllers\Api\QrAttendanceController::class, 'scan'])->name('student.qr-scan');
});

// Admin dashboard route
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // QR Attendance page
    Route::get('/admin/qr-attendance', function () {
        $locations = \App\Models\Location::withCount('users')->get();
        return view('admin.qr-attendance', compact('locations'));
    })->name('admin.qr-attendance');
    
    // QR code display
    Route::get('/admin/qr-display/{locationId}', function ($locationId) {
        $location = \App\Models\Location::findOrFail($locationId);
        return view('admin.qr-display', compact('location'));
    })->name('admin.qr-display');
    
    // QR code API endpoints (web-based, using session auth)
    Route::get('/admin/qr-codes/generate/{locationId}', [\App\Http\Controllers\Api\AdminQrCodeController::class, 'generate'])->name('admin.qr-codes.generate');
    Route::get('/admin/qr-codes/image/{locationId}', [\App\Http\Controllers\Api\AdminQrCodeController::class, 'getQrImage'])->name('admin.qr-codes.image');
    Route::post('/admin/qr-codes/revoke/{tokenId}', [\App\Http\Controllers\Api\AdminQrCodeController::class, 'revoke'])->name('admin.qr-codes.revoke');
    
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


// Performance test route - REMOVE IN PRODUCTION
Route::get('/performance-test', function () {
    $results = [];
    
    // Test 1: Database connection
    $start = microtime(true);
    try {
        DB::connection()->getPdo();
        $results['db_connection'] = round((microtime(true) - $start) * 1000, 2) . 'ms';
    } catch (\Exception $e) {
        $results['db_connection'] = 'FAILED: ' . $e->getMessage();
    }
    
    // Test 2: Simple query
    $start = microtime(true);
    try {
        DB::table('users')->count();
        $results['simple_query'] = round((microtime(true) - $start) * 1000, 2) . 'ms';
    } catch (\Exception $e) {
        $results['simple_query'] = 'FAILED: ' . $e->getMessage();
    }
    
    // Test 3: Cache write
    $start = microtime(true);
    try {
        Cache::put('test_key', 'test_value', 60);
        $results['cache_write'] = round((microtime(true) - $start) * 1000, 2) . 'ms';
    } catch (\Exception $e) {
        $results['cache_write'] = 'FAILED: ' . $e->getMessage();
    }
    
    // Test 4: Cache read
    $start = microtime(true);
    try {
        Cache::get('test_key');
        $results['cache_read'] = round((microtime(true) - $start) * 1000, 2) . 'ms';
    } catch (\Exception $e) {
        $results['cache_read'] = 'FAILED: ' . $e->getMessage();
    }
    
    // Test 5: Session
    $start = microtime(true);
    try {
        session(['test' => 'value']);
        $results['session_write'] = round((microtime(true) - $start) * 1000, 2) . 'ms';
    } catch (\Exception $e) {
        $results['session_write'] = 'FAILED: ' . $e->getMessage();
    }
    
    // Test 6: Complex query
    $start = microtime(true);
    try {
        DB::table('attendance_records')
            ->join('users', 'attendance_records.user_id', '=', 'users.id')
            ->select('attendance_records.*', 'users.name')
            ->limit(10)
            ->get();
        $results['complex_query'] = round((microtime(true) - $start) * 1000, 2) . 'ms';
    } catch (\Exception $e) {
        $results['complex_query'] = 'FAILED: ' . $e->getMessage();
    }
    
    // Environment info
    $results['environment'] = [
        'app_env' => config('app.env'),
        'app_debug' => config('app.debug'),
        'cache_driver' => config('cache.default'),
        'session_driver' => config('session.driver'),
        'db_connection' => config('database.default'),
        'db_host' => config('database.connections.pgsql.host'),
    ];
    
    return response()->json($results, 200, [], JSON_PRETTY_PRINT);
});
