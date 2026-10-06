<?php

namespace Database\Factories;

use App\Enums\Weekday;
use App\Models\AvailabilityWindow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AvailabilityWindow>
 */
class AvailabilityWindowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'weekday' => fake()->randomElement(Weekday::cases()),
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'is_active' => true,
        ];
    }

    public function forWeekday(Weekday $weekday, string $startTime = '09:00', string $endTime = '12:00'): static
    {
        return $this->state(fn (): array => [
            'weekday' => $weekday,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
