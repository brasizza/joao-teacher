<?php

namespace Tests\Feature\Livewire;

use App\Livewire\TestimonialForm;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class TestimonialFormTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const string ValidContent = 'As aulas são objetivas e já consigo participar das reuniões em inglês.';

    public function test_page_is_reachable_by_link_but_hidden_from_search_engines(): void
    {
        $this->withoutVite();

        $response = $this->get(route('testimonials.create'));

        $response
            ->assertSeeLivewire(TestimonialForm::class)
            ->assertSee('<meta name="robots" content="noindex, nofollow">', escape: false);
    }

    public function test_submission_is_stored_hidden_until_the_teacher_publishes_it(): void
    {
        $this->travelTo('2026-10-06 10:00:00');

        $component = $this->submitTestimonial(name: ' Paula Ribeiro ', context: 'Advogada · aluna há 1 ano');

        $component
            ->assertHasNoErrors()
            ->assertSet('submitted', true)
            ->assertSee('Muito obrigado!');
        $this->assertDatabaseHas('testimonials', [
            'student_name' => 'Paula Ribeiro',
            'student_context' => 'Advogada · aluna há 1 ano',
            'content' => self::ValidContent,
            'is_active' => false,
            'submitted_at' => '2026-10-06 10:00:10',
        ]);
    }

    public function test_requires_a_name_a_meaningful_text_and_consent_to_publish(): void
    {
        $component = $this->submitTestimonial(name: '', content: 'Muito bom!', consent: false);

        $component->assertHasErrors(['name', 'content', 'consent']);
        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_ignores_submissions_that_fill_the_hidden_field(): void
    {
        $component = $this->submitTestimonial(extra: ['website' => 'https://spam.example']);

        $component->assertSet('submitted', false);
        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_blocks_submissions_beyond_the_hourly_limit_per_visitor(): void
    {
        foreach (range(1, TestimonialForm::MaxAttemptsPerHour) as $attempt) {
            $this->submitTestimonial(name: "Aluno {$attempt}")->assertHasNoErrors();
        }

        $component = $this->submitTestimonial(name: 'Aluno extra');

        $component
            ->assertHasErrors(['form'])
            ->assertSet('submitted', false);
        $this->assertDatabaseCount('testimonials', TestimonialForm::MaxAttemptsPerHour);
    }

    /**
     * @param  array<string, string>  $extra
     */
    private function submitTestimonial(
        string $name = 'Paula Ribeiro',
        string $context = '',
        string $content = self::ValidContent,
        bool $consent = true,
        array $extra = [],
    ): Testable {
        $component = Livewire::test(TestimonialForm::class);

        $this->travel(10)->seconds();

        return $component
            ->set('name', $name)
            ->set('context', $context)
            ->set('content', $content)
            ->set('consent', $consent)
            ->set($extra)
            ->call('submit');
    }
}
