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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('location_id')
                  ->constrained('locations')
                  ->cascadeOnDelete();
            $table->date('date');
            $table->timestamp('time_in');
            $table->timestamp('time_out')->nullable();
            $table->decimal('total_hours', 5, 2)->nullable();
            $table->enum('scan_type', ['time_in', 'time_out']);
            $table->string('scanner_ip', 45)->nullable();
            $table->boolean('is_valid')->default(true);
            $table->text('validation_notes')->nullable();
            $table->timestamps();
            
            // Composite indexes for common queries
            $table->index(['user_id', 'date']);
            $table->index(['location_id', 'date']);
            $table->index('date');
            $table->index('is_valid');
            
            // Unique constraint to prevent duplicate time-ins on same day
            $table->unique(['user_id', 'location_id', 'date', 'scan_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
