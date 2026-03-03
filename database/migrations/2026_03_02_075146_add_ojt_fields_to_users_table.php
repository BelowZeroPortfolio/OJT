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
            $table->string('student_id', 50)->unique()->nullable()->after('id');
            $table->enum('role', ['student', 'admin'])->default('student')->after('password');
            $table->string('course', 100)->nullable()->after('role');
            $table->foreignId('assigned_location_id')
                  ->nullable()
                  ->after('course')
                  ->constrained('locations')
                  ->nullOnDelete();
            $table->softDeletes();
            
            $table->index('student_id');
            $table->index('role');
            $table->index('assigned_location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
            $table->dropIndex(['role']);
            $table->dropIndex(['assigned_location_id']);
            $table->dropForeign(['assigned_location_id']);
            $table->dropColumn(['student_id', 'role', 'course', 'assigned_location_id']);
            $table->dropSoftDeletes();
        });
    }
};
