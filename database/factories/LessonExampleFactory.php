<?php

namespace Database\Factories;

use App\Models\LessonExample;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonExample>
 */
class LessonExampleFactory extends Factory
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

    public function withVideo(string $url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'): static
    {
        return $this->state(fn (): array => ['youtube_url' => $url]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
