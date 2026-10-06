<section id="aulas">
    <div class="mx-auto max-w-6xl px-5 py-24 sm:px-8 lg:py-32">
        <x-site.section-heading
            :number="$number"
            eyebrow="Como são as aulas"
            title="Aulas práticas, pensadas para a sua realidade."
            :intro="$settings->lessons_intro"
        />

        <div class="mt-16 grid gap-x-8 gap-y-14 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($lessonExamples as $lesson)
                <article class="flex flex-col">
                    @if ($lesson->youtube_video_id)
                        <div x-data="{ playing: false }" class="relative aspect-video overflow-hidden bg-ink">
                            <template x-if="playing">
                                <iframe
                                    src="https://www.youtube-nocookie.com/embed/{{ $lesson->youtube_video_id }}?autoplay=1&rel=0"
                                    title="{{ $lesson->title }}"
                                    class="absolute inset-0 size-full"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                ></iframe>
                            </template>

                            <button
                                type="button"
                                x-show="! playing"
                                x-on:click="playing = true"
                                class="group absolute inset-0 size-full"
                                aria-label="Assistir ao vídeo: {{ $lesson->title }}"
                            >
                                <img
                                    src="{{ $lesson->imageUrl() ?? "https://i.ytimg.com/vi/{$lesson->youtube_video_id}/hqdefault.jpg" }}"
                                    alt=""
                                    loading="lazy"
                                    class="size-full object-cover opacity-90 transition duration-500 group-hover:scale-[1.03] group-hover:opacity-100"
                                >
                                <span class="absolute inset-0 grid place-items-center bg-ink/20 transition group-hover:bg-ink/10">
                                    <span class="grid size-16 place-items-center rounded-full bg-paper/95 text-ink shadow-lg transition group-hover:scale-110">
                                        <svg viewBox="0 0 24 24" fill="currentColor" class="ml-1 size-6" aria-hidden="true"><path d="M8 5.14v13.72a1 1 0 0 0 1.52.85l11.2-6.86a1 1 0 0 0 0-1.7L9.52 4.29A1 1 0 0 0 8 5.14Z"/></svg>
                                    </span>
                                </span>
                            </button>
                        </div>
                    @elseif ($lesson->imageUrl())
                        <img src="{{ $lesson->imageUrl() }}" alt="" loading="lazy" class="aspect-video w-full object-cover">
                    @else
                        <div aria-hidden="true" class="relative grid aspect-video place-items-center overflow-hidden bg-paper-deep">
                            <span class="font-serif text-7xl text-brass-light italic">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="absolute inset-4 border border-line"></span>
                        </div>
                    @endif

                    <h3 class="mt-6 font-serif text-2xl leading-snug font-medium">{{ $lesson->title }}</h3>
                    <p class="mt-3 leading-relaxed text-ink-soft">{{ $lesson->description }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
