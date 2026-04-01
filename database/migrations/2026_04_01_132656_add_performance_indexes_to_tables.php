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
        Schema::table('users', function (Blueprint $table) {
            // Add index for name searches (LIKE queries)
            $table->index('name');
            
            // Add index for course filtering
            $table->index('course');
            
            // Composite index for role + course queries
            $table->index(['role', 'course']);
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            // Add index for scan_type filtering (used in statistics)
            $table->index('scan_type');
            
            // Composite index for date + scan_type (common in dashboard stats)
            $table->index(['date', 'scan_type']);
            
            // Composite index for user_id + date + scan_type (prevents duplicates check)
            $table->index(['user_id', 'date', 'scan_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['course']);
            $table->dropIndex(['role', 'course']);
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropIndex(['scan_type']);
            $table->dropIndex(['date', 'scan_type']);
            $table->dropIndex(['user_id', 'date', 'scan_type']);
        });
    }
};
