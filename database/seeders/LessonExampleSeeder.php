<?php

namespace Database\Seeders;

use App\Models\LessonExample;
use Illuminate\Database\Seeder;

class LessonExampleSeeder extends Seeder
{
    /**
     * Seed starter examples of how lessons are taught.
     */
    public function run(): void
    {
        if (LessonExample::query()->exists()) {
            return;
        }

        $examples = [
            [
                'title' => 'Conversação guiada',
                'description' => 'Simulações de situações reais — reuniões, apresentações, small talk — com correção em tempo real e um repertório de expressões naturais.',
            ],
            [
                'title' => 'Inglês para negócios',
                'description' => 'E-mails, calls e negociações: praticamos o vocabulário e o tom certos para o seu dia a dia profissional.',
            ],
            [
                'title' => 'Preparação para exames',
                'description' => 'Estratégias e simulados para IELTS, TOEFL e Cambridge, com foco nas habilidades que mais pesam na sua nota.',
            ],
        ];

        foreach ($examples as $position => $example) {
            LessonExample::query()->create([...$example, 'sort_order' => $position + 1]);
        }
    }
}
