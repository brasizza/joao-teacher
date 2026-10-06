<?php

namespace App\Livewire;

use App\Livewire\Concerns\ProtectsAgainstSpam;
use App\Models\Testimonial;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Public form where students leave a testimonial. Every submission is stored
 * hidden from the site until the teacher publishes it in the admin panel.
 */
class TestimonialForm extends Component
{
    use ProtectsAgainstSpam;

    /**
     * Submissions allowed per IP address in one hour.
     */
    public const int MaxAttemptsPerHour = 3;

    public string $name = '';

    public string $context = '';

    public string $content = '';

    public bool $consent = false;

    public bool $submitted = false;

    public function submit(): void
    {
        if ($this->isLikelySpam() || $this->exceedsHourlyLimit('testimonial-submission', self::MaxAttemptsPerHour)) {
            return;
        }

        $validated = $this->validate(
            [
                'name' => ['required', 'string', 'min:2', 'max:120'],
                'context' => ['nullable', 'string', 'max:120'],
                'content' => ['required', 'string', 'min:20', 'max:1200'],
                'consent' => ['accepted'],
            ],
            [
                'name.required' => 'Informe seu nome.',
                'name.min' => 'Informe seu nome.',
                'content.required' => 'Escreva seu depoimento.',
                'content.min' => 'Conte um pouco mais: escreva pelo menos :min caracteres.',
                'consent.accepted' => 'Precisamos da sua autorização para publicar o depoimento.',
            ],
        );

        $this->countSubmission('testimonial-submission');

        Testimonial::query()->create([
            'student_name' => trim($validated['name']),
            'student_context' => filled($validated['context']) ? trim($validated['context']) : null,
            'content' => trim($validated['content']),
            'submitted_at' => now(),
            'is_active' => false,
        ]);

        $this->reset('name', 'context', 'content', 'consent');
        $this->submitted = true;
    }

    public function render(): View
    {
        return view('livewire.testimonial-form');
    }
}
