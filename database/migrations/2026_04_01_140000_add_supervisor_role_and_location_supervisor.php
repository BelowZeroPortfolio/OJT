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
        // Update the role enum to include 'supervisor'
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(20)");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('student', 'admin', 'supervisor'))");
        
        // Add supervisor_id to locations table
        Schema::table('locations', function (Blueprint $table) {
            $table->foreignId('supervisor_id')
                  ->nullable()
                  ->after('is_active')
                  ->constrained('users')
                  ->nullOnDelete();
            
            $table->index('supervisor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove supervisor_id from locations
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropIndex(['supervisor_id']);
            $table->dropColumn('supervisor_id');
        });
        
        // Revert role enum back to original
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
        DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(20)");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('student', 'admin'))");
    }
};
