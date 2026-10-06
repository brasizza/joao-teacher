<?php

namespace Database\Factories;

use App\Models\ScheduleBlock;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduleBlock>
 */
class ScheduleBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = CarbonImmutable::instance(fake()->dateTimeBetween('+1 day', '+1 month'))->startOfHour();

        return [
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addHours(2),
            'reason' => fake()->sentence(3),
        ];
    }

    public function between(string $startsAt, string $endsAt): static
    {
        return $this->state(fn (): array => [
            'starts_at' => CarbonImmutable::parse($startsAt),
            'ends_at' => CarbonImmutable::parse($endsAt),
        ]);
    }
}
