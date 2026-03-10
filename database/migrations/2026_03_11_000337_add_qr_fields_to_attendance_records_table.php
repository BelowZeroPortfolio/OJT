<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->enum('scan_method', ['rfid', 'qr_code'])->default('rfid')->after('scan_type');
            $table->foreignId('qr_token_id')->nullable()->after('scan_method')->constrained('qr_attendance_tokens')->nullOnDelete();
            $table->decimal('scan_latitude', 10, 8)->nullable()->after('scanner_ip');
            $table->decimal('scan_longitude', 11, 8)->nullable()->after('scan_latitude');
            $table->boolean('geofence_verified')->default(false)->after('scan_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropForeign(['qr_token_id']);
            $table->dropColumn(['scan_method', 'qr_token_id', 'scan_latitude', 'scan_longitude', 'geofence_verified']);
        });
    }
};
