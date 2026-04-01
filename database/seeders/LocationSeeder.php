<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 5 training locations with supervisors
        $locationsData = [
            [
                'location_code' => 'TECH001',
                'name' => 'Tech Solutions Inc.',
                'address' => '123 Innovation Drive, Metro Manila',
                'is_active' => true,
                'supervisor' => [
                    'name' => 'John Supervisor',
                    'email' => 'john.supervisor@tech.com',
                ],
            ],
            [
                'location_code' => 'GLOB002',
                'name' => 'Global Business Hub',
                'address' => '456 Commerce Avenue, Makati City',
                'is_active' => true,
                'supervisor' => [
                    'name' => 'Maria Santos',
                    'email' => 'maria.santos@global.com',
                ],
            ],
            [
                'location_code' => 'HEAL003',
                'name' => 'Healthcare Partners',
                'address' => '789 Wellness Street, Quezon City',
                'is_active' => true,
                'supervisor' => [
                    'name' => 'Robert Cruz',
                    'email' => 'robert.cruz@healthcare.com',
                ],
            ],
            [
                'location_code' => 'ENGR004',
                'name' => 'Engineering Works Corp',
                'address' => '321 Industrial Park, Pasig City',
                'is_active' => true,
                'supervisor' => [
                    'name' => 'Ana Reyes',
                    'email' => 'ana.reyes@engineering.com',
                ],
            ],
            [
                'location_code' => 'DIGI005',
                'name' => 'Digital Marketing Agency',
                'address' => '654 Creative Boulevard, Taguig City',
                'is_active' => true,
                'supervisor' => [
                    'name' => 'Carlos Garcia',
                    'email' => 'carlos.garcia@digital.com',
                ],
            ],
        ];

        foreach ($locationsData as $data) {
            // Create supervisor first
            $supervisor = \App\Models\User::create([
                'name' => $data['supervisor']['name'],
                'email' => $data['supervisor']['email'],
                'password' => \Hash::make('supervisor123'),
                'role' => 'supervisor',
                'email_verified_at' => now(),
            ]);

            // Create location with supervisor
            Location::create([
                'location_code' => $data['location_code'],
                'name' => $data['name'],
                'address' => $data['address'],
                'is_active' => $data['is_active'],
                'supervisor_id' => $supervisor->id,
            ]);
        }

        $this->command->info('Created 5 locations with 5 supervisors.');
    }
}
