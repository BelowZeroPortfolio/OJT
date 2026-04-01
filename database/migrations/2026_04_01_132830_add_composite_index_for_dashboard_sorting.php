<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add composite index for dashboard sorting (date DESC, time_in DESC)
        // This will dramatically speed up the main dashboard query
        DB::statement('CREATE INDEX attendance_records_date_time_in_desc_index ON attendance_records (date DESC, time_in DESC)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS attendance_records_date_time_in_desc_index');
    }
};
