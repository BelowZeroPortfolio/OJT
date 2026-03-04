<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UpdateStudentsWithRfidSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Update existing students with RFID numbers
        $students = User::where('role', 'student')->get();
        
        foreach ($students as $index => $student) {
            // Generate a simple RFID number (in real scenario, these would be from actual RFID cards)
            $rfidNumber = 'RFID' . str_pad($index + 1, 6, '0', STR_PAD_LEFT);
            
            $student->update([
                'rfid_number' => $rfidNumber
            ]);
        }
        
        $this->command->info('Updated ' . $students->count() . ' students with RFID numbers.');
        $this->command->info('Sample RFID numbers: RFID000001, RFID000002, etc.');
    }
}