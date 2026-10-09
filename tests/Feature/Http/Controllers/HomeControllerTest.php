<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\LessonExample;
use App\Models\MethodologyStep;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_renders_the_teacher_profile_and_whatsapp_contact(): void
    {
        SiteSetting::factory()->create([
            'teacher_name' => 'Teacher Ana',
            'headline' => 'Fale inglês com confiança.',
            'whatsapp_number' => '5511987654321',
            'whatsapp_general_message' => 'Quero saber mais',
        ]);

        $response = $this->get(route('home'));

        $response
            ->assertSee('Teacher Ana')
            ->assertSee('Fale inglês com confiança.')
            ->assertSee('https://wa.me/5511987654321?text=Quero%20saber%20mais', escape: false);
    }

    public function test_renders_default_content_before_the_database_is_seeded(): void
    {
        $response = $this->get(route('home'));

        $response->assertSee('Teacher João');
        $this->assertDatabaseCount('site_settings', 1);
    }

    public function test_renders_the_site_in_the_primary_color_chosen_by_the_teacher(): void
    {
        SiteSetting::factory()->create(['primary_color' => '#14532d']);

        $response = $this->get(route('home'));

        $response
            ->assertSee('--color-ink: #14532d;', escape: false)
            ->assertSee('<meta name="theme-color" content="#14532d">', escape: false);
    }

    public function test_keeps_the_original_colors_when_no_primary_color_is_chosen(): void
    {
        SiteSetting::factory()->create(['primary_color' => null]);

        $response = $this->get(route('home'));

        $response
            ->assertDontSee('--color-ink', escape: false)
            ->assertSee('<meta name="theme-color" content="#1e2a44">', escape: false);
    }

    public function test_shows_only_active_methodology_steps_in_their_display_order(): void
    {
        SiteSetting::factory()->create();
        MethodologyStep::factory()->create(['title' => 'Segunda etapa', 'sort_order' => 2]);
        MethodologyStep::factory()->create(['title' => 'Primeira etapa', 'sort_order' => 1]);
        MethodologyStep::factory()->inactive()->create(['title' => 'Etapa oculta']);

        $response = $this->get(route('home'));

        $response
            ->assertSeeInOrder(['Primeira etapa', 'Segunda etapa'])
            ->assertDontSee('Etapa oculta');
    }

    public function test_hides_inactive_lessons_and_testimonials(): void
    {
        SiteSetting::factory()->create();
        LessonExample::factory()->inactive()->create(['title' => 'Aula escondida']);
        Testimonial::factory()->inactive()->create(['student_name' => 'Aluno Escondido']);

        $response = $this->get(route('home'));

        $response
            ->assertDontSee('Aula escondida')
            ->assertDontSee('Aluno Escondido')
            ->assertDontSee('id="aulas"', escape: false)
            ->assertDontSee('id="depoimentos"', escape: false);
    }

    public function test_embeds_lesson_videos_without_tracking_cookies(): void
    {
        SiteSetting::factory()->create();
        LessonExample::factory()->withVideo('https://youtu.be/dQw4w9WgXcQ')->create();

        $response = $this->get(route('home'));

        $response->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', escape: false);
    }

    public function test_escapes_html_typed_into_testimonials(): void
    {
        SiteSetting::factory()->create();
        Testimonial::factory()->create(['content' => '<script>alert("x")</script>']);

        $response = $this->get(route('home'));

        $response
            ->assertDontSee('<script>alert("x")</script>', escape: false)
            ->assertSee('&lt;script&gt;', escape: false);
    }
}
