<section id="depoimentos" class="border-t border-line bg-paper-deep/60">
    <div class="mx-auto max-w-6xl px-5 py-24 sm:px-8 lg:py-32">
        <x-site.section-heading
            :number="$number"
            eyebrow="Depoimentos"
            title="Resultados que falam por si."
            :intro="$settings->testimonials_intro"
        />

        <div class="mt-16 grid gap-8 lg:grid-cols-3">
            @foreach ($testimonials as $testimonial)
                <figure class="relative flex flex-col bg-paper p-8 ring-1 ring-line sm:p-10">
                    <span aria-hidden="true" class="absolute -top-5 left-8 font-serif text-7xl leading-none text-brass-light">“</span>

                    <blockquote class="flex-1 font-serif text-xl leading-relaxed text-ink">
                        <x-site.paragraphs :text="$testimonial->content" class="mt-4 first:mt-0" />
                    </blockquote>

                    <figcaption class="mt-8 flex items-center gap-4 border-t border-line pt-6">
                        @if ($testimonial->photoUrl())
                            <img src="{{ $testimonial->photoUrl() }}" alt="" loading="lazy" class="size-12 rounded-full object-cover">
                        @else
                            <span aria-hidden="true" class="grid size-12 place-items-center rounded-full bg-ink text-sm font-semibold tracking-wide text-paper">
                                {{ $testimonial->initials() }}
                            </span>
                        @endif

                        <div>
                            <p class="font-semibold text-ink">{{ $testimonial->student_name }}</p>
                            @if (filled($testimonial->student_context))
                                <p class="text-sm text-ink-soft">{{ $testimonial->student_context }}</p>
                            @endif
                        </div>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
