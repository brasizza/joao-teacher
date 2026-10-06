@php
    $navigation = collect([
        'sobre' => ['label' => 'Sobre', 'visible' => filled($settings->about_text)],
        'metodologia' => ['label' => 'Metodologia', 'visible' => $methodologySteps->isNotEmpty()],
        'aulas' => ['label' => 'Aulas', 'visible' => $lessonExamples->isNotEmpty()],
        'depoimentos' => ['label' => 'Depoimentos', 'visible' => $testimonials->isNotEmpty()],
        'agenda' => ['label' => 'Agenda', 'visible' => true],
    ])->filter(fn (array $item): bool => $item['visible'])->map(fn (array $item): string => $item['label']);

    $sectionNumber = fn (string $anchor): string => str_pad((string) ($navigation->keys()->search($anchor) + 1), 2, '0', STR_PAD_LEFT);
@endphp

<x-layouts.site
    :title="$settings->seo_title ?: $settings->teacher_name"
    :description="$settings->seo_description"
    :image="$settings->photoUrl()"
    :monogram="$settings->monogram()"
>
    @include('home.header')

    <main>
        @include('home.hero')

        @if ($navigation->has('sobre'))
            @include('home.about', ['number' => $sectionNumber('sobre')])
        @endif

        @if ($navigation->has('metodologia'))
            @include('home.methodology', ['number' => $sectionNumber('metodologia')])
        @endif

        @if ($navigation->has('aulas'))
            @include('home.lessons', ['number' => $sectionNumber('aulas')])
        @endif

        @if ($navigation->has('depoimentos'))
            @include('home.testimonials', ['number' => $sectionNumber('depoimentos')])
        @endif

        @include('home.schedule', ['number' => $sectionNumber('agenda')])
    </main>

    @include('home.footer')

    @if ($whatsAppUrl)
        <a
            href="{{ $whatsAppUrl }}"
            target="_blank"
            rel="noopener"
            aria-label="Falar no WhatsApp"
            class="fixed right-5 bottom-5 z-50 grid size-14 place-items-center rounded-full bg-whatsapp text-white shadow-[0_12px_30px_-8px_rgb(31_168_85/0.7)] transition hover:scale-105 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-whatsapp"
        >
            <x-site.whatsapp-icon class="size-7" />
        </a>
    @endif
</x-layouts.site>
