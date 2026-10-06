<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\BookingRequest;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRequest>
 */
class BookingRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = CarbonImmutable::instance(fake()->dateTimeBetween('+1 day', '+2 weeks'))->startOfHour();

        return [
            'name' => fake()->name(),
            'phone' => '55119'.fake()->numerify('########'),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addMinutes(30),
            'status' => BookingStatus::Pending,
        ];
    }

    public function startingAt(string $startsAt, int $durationMinutes = 30): static
    {
        return $this->state(fn (): array => [
            'starts_at' => CarbonImmutable::parse($startsAt),
            'ends_at' => CarbonImmutable::parse($startsAt)->addMinutes($durationMinutes),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (): array => ['status' => BookingStatus::Pending]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (): array => [
            'status' => BookingStatus::Confirmed,
            'confirmed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}
