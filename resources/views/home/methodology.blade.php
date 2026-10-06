<section id="metodologia" class="bg-paper-deep">
    <div class="mx-auto max-w-6xl px-5 py-24 sm:px-8 lg:py-32">
        <x-site.section-heading
            :number="$number"
            eyebrow="Metodologia"
            title="Um método claro, do diagnóstico à fluência."
            :intro="$settings->methodology_intro"
        />

        <ol class="mt-16 grid gap-x-10 gap-y-14 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($methodologySteps as $step)
                <li class="border-t-2 border-ink pt-6">
                    <span aria-hidden="true" class="font-serif text-5xl font-medium text-brass">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <h3 class="mt-6 font-serif text-2xl leading-snug font-medium">{{ $step->title }}</h3>
                    <p class="mt-3 leading-relaxed text-ink-soft">{{ $step->description }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>
