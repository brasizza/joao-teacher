<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Seed fictional testimonials that show the layout (local only).
     */
    public function run(): void
    {
        if (Testimonial::query()->exists()) {
            return;
        }

        $testimonials = [
            [
                'student_name' => 'Mariana Alves',
                'student_context' => 'Gerente de projetos · aluna há 2 anos',
                'content' => 'Em poucos meses passei a conduzir reuniões com o time de Londres sem travar. As aulas são objetivas e o feedback é sempre muito preciso.',
            ],
            [
                'student_name' => 'Rafael Costa',
                'student_context' => 'Engenheiro de software',
                'content' => 'Precisava de inglês para entrevistas internacionais. O plano foi direto ao ponto e consegui a vaga que eu queria.',
            ],
            [
                'student_name' => 'Beatriz Lima',
                'student_context' => 'Aprovada no IELTS 7.5',
                'content' => 'A preparação para o IELTS foi impecável: estratégia, simulados e muita prática de speaking. Superei a nota que eu precisava.',
            ],
        ];

        foreach ($testimonials as $position => $testimonial) {
            Testimonial::query()->create([...$testimonial, 'sort_order' => $position + 1]);
        }
    }
}
