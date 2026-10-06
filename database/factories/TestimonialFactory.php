<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_name' => fake()->name(),
            'student_context' => fake()->jobTitle(),
            'content' => fake()->paragraph(),
            'is_active' => true,
        ];
    }

    public function submittedByStudent(): static
    {
        return $this->state(fn (): array => [
            'submitted_at' => now(),
            'is_active' => false,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
