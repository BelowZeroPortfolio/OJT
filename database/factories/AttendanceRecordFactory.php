<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceRecord>
 */
class AttendanceRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-30 days', 'now');
        $timeIn = (clone $date)->setTime(
            fake()->numberBetween(7, 9),
            fake()->numberBetween(0, 59)
        );
        
        // 80% chance of having time_out
        $hasTimeOut = fake()->boolean(80);
        $timeOut = null;
        $totalHours = null;
        
        if ($hasTimeOut) {
            $timeOut = (clone $timeIn)->modify('+' . fake()->numberBetween(6, 10) . ' hours');
            $totalHours = round(($timeOut->getTimestamp() - $timeIn->getTimestamp()) / 3600, 2);
        }

        return [
            'user_id' => null, // Will be set by seeder or test
            'location_id' => null, // Will be set by seeder or test
            'date' => $date->format('Y-m-d'),
            'time_in' => $timeIn,
            'time_out' => $timeOut,
            'total_hours' => $totalHours,
            'scan_type' => 'time_in',
            'scanner_ip' => fake()->ipv4(),
            'is_valid' => fake()->boolean(95), // 95% valid
            'validation_notes' => fake()->boolean(10) ? fake()->sentence() : null,
        ];
    }

    /**
     * Indicate that the attendance record is complete (has time_out).
     *
     * @return $this
     */
    public function complete(): static
    {
        return $this->state(function (array $attributes) {
            $timeIn = \Carbon\Carbon::parse($attributes['time_in']);
            $timeOut = (clone $timeIn)->addHours(fake()->numberBetween(6, 10));
            $totalHours = round($timeOut->diffInMinutes($timeIn) / 60, 2);

            return [
                'time_out' => $timeOut,
                'total_hours' => $totalHours,
            ];
        });
    }

    /**
     * Indicate that the attendance record is incomplete (no time_out).
     *
     * @return $this
     */
    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'time_out' => null,
            'total_hours' => null,
        ]);
    }

    /**
     * Indicate that the attendance record is invalid.
     *
     * @return $this
     */
    public function invalid(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_valid' => false,
            'validation_notes' => fake()->sentence(),
        ]);
    }
}
