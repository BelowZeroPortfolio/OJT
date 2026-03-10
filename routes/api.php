<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\RfidAttendanceController;
use App\Http\Controllers\Api\QrAttendanceController;
use App\Http\Controllers\Api\AdminQrCodeController;
use App\Http\Controllers\Api\StudentAttendanceController;
use App\Http\Controllers\Api\AdminAttendanceController;
use App\Http\Controllers\Api\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API authentication routes
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');

// RFID scanner endpoint with rate limiting
Route::post('/rfid/scan', [RfidAttendanceController::class, 'scan'])
    ->middleware('throttle:scanner');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Student attendance API endpoint
Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    Route::get('/student/attendance', [StudentAttendanceController::class, 'index']);
    Route::post('/student/rfid-attendance', [StudentAttendanceController::class, 'rfidAttendance']);
    
    // QR code attendance scanning
    Route::post('/attendance/qr-scan', [QrAttendanceController::class, 'scan']);
});

// Admin attendance API endpoint
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/attendance', [AdminAttendanceController::class, 'index']);
    
    // QR code generation and management
    Route::get('/admin/qr-codes/generate/{locationId}', [AdminQrCodeController::class, 'generate']);
    Route::get('/admin/qr-codes/image/{locationId}', [AdminQrCodeController::class, 'getQrImage']);
    Route::post('/admin/qr-codes/revoke/{tokenId}', [AdminQrCodeController::class, 'revoke']);
    
    // Report generation endpoints
    Route::post('/admin/reports/generate', [ReportController::class, 'generate']);
    Route::get('/admin/reports/{id}/download', [ReportController::class, 'download']);
    Route::get('/admin/reports/{id}/status', [ReportController::class, 'status']);
});
