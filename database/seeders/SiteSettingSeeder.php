<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Fill the site with starter copy the teacher can edit in the panel.
     */
    public function run(): void
    {
        if (SiteSetting::query()->exists()) {
            return;
        }

        SiteSetting::query()->create([
            ...SiteSetting::defaults(),
            'tagline' => 'English Teacher · Aulas particulares online',
            'headline' => 'Inglês com método, clareza e confiança.',
            'subheadline' => 'Aulas particulares para adultos que precisam se comunicar de verdade — no trabalho, em viagens e em exames internacionais. Um plano feito para os seus objetivos, conduzido do primeiro ao último minuto em inglês.',
            'highlights' => [
                ['value' => '10+', 'label' => 'anos ensinando inglês'],
                ['value' => '500+', 'label' => 'alunos acompanhados'],
                ['value' => 'C2', 'label' => 'proficiência certificada'],
            ],
            'about_title' => 'Ensino inglês como quem constrói uma ponte: com método, paciência e propósito.',
            'about_text' => "Sou professor de inglês há mais de dez anos e já acompanhei centenas de alunos — de iniciantes a executivos que precisavam apresentar resultados em reuniões internacionais.\n\nAcredito que aprender um idioma é ganhar autonomia. Por isso, cada aula é planejada a partir das suas necessidades reais, com materiais atualizados, correção precisa e muita prática de conversação.\n\nMeu compromisso é simples: que você saia de cada aula falando um pouco mais, entendendo um pouco melhor e com mais confiança para usar o inglês no seu dia a dia.",
            'methodology_intro' => 'Um caminho claro, com etapas bem definidas e progresso que você consegue medir.',
            'lessons_intro' => 'Cada encontro é dinâmico, prático e pensado para o seu contexto. Veja alguns formatos de aula.',
            'testimonials_intro' => 'O que dizem alunos que já passaram por aqui.',
            'schedule_intro' => 'Escolha o melhor horário para uma aula experimental gratuita. Você recebe a confirmação pelo WhatsApp.',
            'seo_title' => 'Teacher João — Aulas particulares de inglês',
            'seo_description' => 'Aulas particulares de inglês online com método, foco em conversação e plano personalizado. Agende sua aula experimental.',
        ]);
    }
}
