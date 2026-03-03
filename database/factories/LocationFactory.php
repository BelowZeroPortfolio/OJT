<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_code' => 'LOC-' . strtoupper(fake()->unique()->bothify('???###')),
            'name' => fake()->company() . ' Training Center',
            'address' => fake()->address(),
            'is_active' => fake()->boolean(90), // 90% chance of being active
        ];
    }
}
