<?php

namespace Database\Seeders;

use App\Models\MethodologyStep;
use Illuminate\Database\Seeder;

class MethodologyStepSeeder extends Seeder
{
    /**
     * Seed the starter methodology pillars.
     */
    public function run(): void
    {
        if (MethodologyStep::query()->exists()) {
            return;
        }

        $steps = [
            [
                'title' => 'Diagnóstico de nível',
                'description' => 'Começamos com uma avaliação completa de fala, escuta, leitura e escrita, tendo como referência o Quadro Europeu (CEFR). Você sabe exatamente de onde parte e aonde vai chegar.',
            ],
            [
                'title' => 'Plano sob medida',
                'description' => 'Carreira, viagens, certificações ou entrevistas: o conteúdo é montado para os seus objetivos. Nada de apostila genérica.',
            ],
            [
                'title' => 'Comunicação desde a primeira aula',
                'description' => 'Aulas conduzidas em inglês, com foco em conversação real, pronúncia e vocabulário útil. A gramática entra a serviço da comunicação.',
            ],
            [
                'title' => 'Acompanhamento e feedback',
                'description' => 'Correções precisas, material de revisão após cada encontro e reavaliações periódicas para medir sua evolução de forma objetiva.',
            ],
        ];

        foreach ($steps as $position => $step) {
            MethodologyStep::query()->create([...$step, 'sort_order' => $position + 1]);
        }
    }
}
