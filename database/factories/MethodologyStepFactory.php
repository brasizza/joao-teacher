<?php

namespace Database\Factories;

use App\Models\MethodologyStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MethodologyStep>
 */
class MethodologyStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
