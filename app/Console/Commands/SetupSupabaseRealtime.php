<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SetupSupabaseRealtime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supabase:setup-realtime {--test : Test connection only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Supabase real-time functionality with RLS policies';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Setting up Supabase Real-time Functionality');
        $this->newLine();

        // Check if we're using Supabase
        $connection = config('database.default');
        $host = config('database.connections.' . $connection . '.host');

        if (!str_contains($host, 'supabase.co')) {
            $this->warn('⚠️  Not using Supabase database. Current connection: ' . $connection);
            $this->info('To use Supabase real-time:');
            $this->info('1. Update your .env file with Supabase credentials');
            $this->info('2. Set DB_CONNECTION=pgsql');
            $this->info('3. Run this command again');
            return 1;
        }

        $this->info('✅ Detected Supabase connection: ' . $host);

        // Test connection
        try {
            DB::select('SELECT 1 as test');
            $this->info('✅ Database connection successful');
        } catch (\Exception $e) {
            $this->error('❌ Database connection failed: ' . $e->getMessage());
            $this->info('Please check your Supabase credentials in .env file');
            return 1;
        }

        if ($this->option('test')) {
            $this->info('✅ Connection test completed successfully');
            return 0;
        }

        // Check if migration file exists
        $migrationFile = database_path('supabase_migration.sql');
        if (!File::exists($migrationFile)) {
            $this->error('❌ Migration file not found: ' . $migrationFile);
            return 1;
        }

        $this->info('📄 Found migration file: supabase_migration.sql');
        $this->newLine();

        $this->warn('⚠️  IMPORTANT: This command will show you the SQL to run in Supabase');
        $this->warn('   You need to manually execute it in your Supabase SQL Editor');
        $this->newLine();

        if (!$this->confirm('Do you want to see the migration SQL?')) {
            $this->info('Operation cancelled');
            return 0;
        }

        // Read and display the migration SQL
        $sql = File::get($migrationFile);
        
        $this->info('📋 Copy and paste this SQL into your Supabase SQL Editor:');
        $this->newLine();
        $this->line('='.str_repeat('=', 80));
        $this->line($sql);
        $this->line('='.str_repeat('=', 80));
        $this->newLine();

        $this->info('📝 Instructions:');
        $this->info('1. Go to your Supabase Dashboard');
        $this->info('2. Navigate to SQL Editor');
        $this->info('3. Create a new query');
        $this->info('4. Paste the SQL above');
        $this->info('5. Run the query');
        $this->newLine();

        $this->info('🔧 After running the SQL:');
        $this->info('1. Real-time subscriptions will be enabled');
        $this->info('2. Row Level Security policies will be applied');
        $this->info('3. Your Laravel app will receive real-time updates');
        $this->newLine();

        $this->info('🧪 Test your setup:');
        $this->info('   Visit: ' . url('/test-supabase'));
        $this->newLine();

        $this->info('✨ Setup instructions displayed successfully!');
        
        return 0;
    }
}
