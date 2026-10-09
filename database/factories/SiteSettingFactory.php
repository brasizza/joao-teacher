<?php

namespace Database\Factories;

use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_name' => 'Teacher '.fake()->firstName(),
            'tagline' => 'English Teacher',
            'headline' => fake()->sentence(5),
            'subheadline' => fake()->sentence(12),
            'primary_color' => null,
            'highlights' => [
                ['value' => '10+', 'label' => 'anos de experiência'],
                ['value' => 'C2', 'label' => 'Cambridge Proficiency'],
            ],
            'about_title' => fake()->sentence(4),
            'about_text' => fake()->paragraphs(2, true),
            'methodology_intro' => fake()->sentence(10),
            'lessons_intro' => fake()->sentence(10),
            'testimonials_intro' => fake()->sentence(10),
            'schedule_intro' => fake()->sentence(10),
            'whatsapp_number' => '5511987654321',
            'whatsapp_general_message' => 'Olá! Gostaria de saber mais sobre as aulas.',
            'whatsapp_booking_message' => 'Olá! Sou {nome} e quero uma aula experimental em {data} às {hora}.',
            'email' => fake()->safeEmail(),
            'seo_title' => fake()->sentence(4),
            'seo_description' => fake()->sentence(15),
            'slot_interval_minutes' => 30,
            'booking_window_days' => 14,
            'minimum_notice_hours' => 0,
            'pending_hold_hours' => null,
        ];
    }

    public function withoutWhatsApp(): static
    {
        return $this->state(fn (): array => ['whatsapp_number' => null]);
    }
}
