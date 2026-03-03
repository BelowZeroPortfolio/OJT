<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'completed', 'failed']);
        $completedAt = $status === 'completed' ? fake()->dateTimeBetween('-7 days', 'now') : null;

        return [
            'generated_by' => null, // Will be set by seeder or test
            'report_type' => fake()->randomElement(['daily', 'weekly', 'monthly']),
            'format' => fake()->randomElement(['csv', 'pdf']),
            'filters' => [
                'student' => fake()->boolean(30) ? fake()->name() : null,
                'course' => fake()->boolean(30) ? fake()->randomElement(['Computer Science', 'IT', 'Business']) : null,
                'location' => fake()->boolean(30) ? 'LOC-' . fake()->bothify('???###') : null,
            ],
            'file_path' => $status === 'completed' ? 'reports/' . fake()->uuid() . '.csv' : null,
            'status' => $status,
            'completed_at' => $completedAt,
        ];
    }

    /**
     * Indicate that the report is pending.
     *
     * @return $this
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'file_path' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the report is completed.
     *
     * @return $this
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'file_path' => 'reports/' . fake()->uuid() . '.' . $attributes['format'],
            'completed_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the report failed.
     *
     * @return $this
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'file_path' => null,
            'completed_at' => null,
        ]);
    }
}
