<section id="inicio" class="relative overflow-hidden">
    <div aria-hidden="true" class="pointer-events-none absolute inset-y-0 right-0 hidden w-[38%] bg-paper-deep lg:block"></div>

    <div class="relative mx-auto grid max-w-6xl gap-14 px-5 pt-14 pb-20 sm:px-8 lg:grid-cols-12 lg:gap-10 lg:pt-24 lg:pb-28">
        <div class="flex flex-col justify-center lg:col-span-7">
            @if (filled($settings->tagline))
                <p class="flex items-center gap-3 text-xs font-semibold tracking-[0.22em] text-brass uppercase">
                    <span aria-hidden="true" class="h-px w-10 bg-current"></span>
                    {{ $settings->tagline }}
                </p>
            @endif

            <h1 class="mt-6 font-serif text-5xl leading-[1.04] font-medium tracking-tight text-balance sm:text-6xl lg:text-7xl">
                {{ $settings->headline }}
            </h1>

            @if (filled($settings->subheadline))
                <p class="mt-7 max-w-xl text-lg leading-relaxed text-pretty text-ink-soft">
                    {{ $settings->subheadline }}
                </p>
            @endif

            <div class="mt-10 flex flex-wrap items-center gap-x-6 gap-y-3">
                <a href="#agenda" class="inline-flex items-center gap-3 rounded-full bg-ink px-7 py-4 text-base font-medium text-paper shadow-[0_14px_30px_-14px_rgb(30_42_68/0.8)] transition hover:bg-ink-deep">
                    Agendar aula experimental
                    <span aria-hidden="true">→</span>
                </a>

                @if ($navigation->has('metodologia'))
                    <a href="#metodologia" class="group inline-flex items-center gap-2 py-4 text-base font-medium text-ink underline decoration-brass-light decoration-1 underline-offset-8 transition hover:decoration-ink">
                        Conheça a metodologia
                    </a>
                @endif
            </div>
        </div>

        <div class="lg:col-span-5">
            <figure class="relative mx-auto max-w-sm lg:mr-0 lg:ml-auto">
                <div aria-hidden="true" class="absolute inset-0 translate-x-4 translate-y-4 border border-brass-light sm:translate-x-6 sm:translate-y-6"></div>

                @if ($settings->photoUrl())
                    <img
                        src="{{ $settings->photoUrl() }}"
                        alt="Foto de {{ $settings->teacher_name }}"
                        class="relative aspect-[4/5] w-full object-cover shadow-[0_30px_60px_-30px_rgb(20_29_49/0.6)]"
                        fetchpriority="high"
                    >
                @else
                    <div class="relative grid aspect-[4/5] w-full place-items-center overflow-hidden bg-ink shadow-[0_30px_60px_-30px_rgb(20_29_49/0.6)]">
                        <span aria-hidden="true" class="font-serif text-[10rem] leading-none text-brass-light/90 italic">{{ $settings->monogram() }}</span>
                        <span aria-hidden="true" class="absolute inset-6 border border-paper/10"></span>
                    </div>
                @endif
            </figure>
        </div>
    </div>

    @if (filled($settings->highlights))
        <div class="relative border-y border-line bg-paper">
            <dl class="mx-auto grid max-w-6xl grid-cols-2 divide-line px-5 sm:px-8 md:grid-flow-col md:auto-cols-fr md:grid-cols-none md:divide-x">
                @foreach ($settings->highlights as $highlight)
                    <div class="flex flex-col-reverse gap-1 py-7 md:px-8 md:first:pl-0">
                        <dt class="text-xs font-medium tracking-[0.14em] text-ink-soft uppercase">{{ $highlight['label'] }}</dt>
                        <dd class="font-serif text-4xl font-medium tracking-tight text-ink">{{ $highlight['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endif
</section>
