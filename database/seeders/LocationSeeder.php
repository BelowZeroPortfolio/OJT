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
        // Create 8 realistic training locations
        $locations = [
            [
                'location_code' => 'TECH001',
                'name' => 'Tech Solutions Inc. Training Center',
                'address' => '123 Innovation Drive, Metro Manila',
                'is_active' => true,
            ],
            [
                'location_code' => 'GLOB002',
                'name' => 'Global Business Hub',
                'address' => '456 Commerce Avenue, Makati City',
                'is_active' => true,
            ],
            [
                'location_code' => 'HEAL003',
                'name' => 'Healthcare Partners Medical Center',
                'address' => '789 Wellness Street, Quezon City',
                'is_active' => true,
            ],
            [
                'location_code' => 'ENGR004',
                'name' => 'Engineering Works Corporation',
                'address' => '321 Industrial Park, Pasig City',
                'is_active' => true,
            ],
            [
                'location_code' => 'DIGI005',
                'name' => 'Digital Marketing Agency',
                'address' => '654 Creative Boulevard, Taguig City',
                'is_active' => true,
            ],
            [
                'location_code' => 'FINA006',
                'name' => 'Financial Services Group',
                'address' => '987 Banking Center, BGC',
                'is_active' => true,
            ],
            [
                'location_code' => 'HOSP007',
                'name' => 'Hospitality Training Institute',
                'address' => '147 Tourism Road, Pasay City',
                'is_active' => true,
            ],
            [
                'location_code' => 'MANU008',
                'name' => 'Manufacturing Excellence Center',
                'address' => '258 Factory Lane, Caloocan City',
                'is_active' => false, // One inactive location for testing
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
