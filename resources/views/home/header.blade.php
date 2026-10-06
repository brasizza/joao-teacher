<header class="sticky top-0 z-40 border-b border-line/70 bg-paper/85 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-6 px-5 sm:h-20 sm:px-8">
        <a href="#inicio" class="flex items-center gap-3">
            <span aria-hidden="true" class="grid size-9 place-items-center rounded-full bg-ink font-serif text-lg text-brass-light">
                {{ $settings->monogram() }}
            </span>
            <span class="font-serif text-lg font-medium tracking-tight">{{ $settings->teacher_name }}</span>
        </a>

        <nav aria-label="Seções" class="hidden items-center gap-8 text-sm font-medium text-ink-soft lg:flex">
            @foreach ($navigation as $anchor => $label)
                <a href="#{{ $anchor }}" class="transition hover:text-ink">{{ $label }}</a>
            @endforeach
        </nav>

        <a href="#agenda" class="rounded-full bg-ink px-5 py-2.5 text-sm font-medium text-paper transition hover:bg-ink-deep">
            Agendar aula
        </a>
    </div>
</header>
