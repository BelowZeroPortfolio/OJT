<?php

use Illuminate\Support\Facades\Route;
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
Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
});

// Admin dashboard route
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // Reports and Activity Logs
    Route::get('/admin/reports', [ReportsController::class, 'index'])->name('admin.reports');
    
    Route::get('/admin/activity-logs', function () {
        return view('admin.activity-logs');
    })->name('admin.activity-logs');
    
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
