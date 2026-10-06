<x-layouts.site
    :title="'Deixe seu depoimento — '.$settings->teacher_name"
    :monogram="$settings->monogram()"
    :indexable="false"
>
    <header class="border-b border-line/70">
        <div class="mx-auto flex h-16 max-w-6xl items-center px-5 sm:h-20 sm:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span aria-hidden="true" class="grid size-9 place-items-center rounded-full bg-ink font-serif text-lg text-brass-light">
                    {{ $settings->monogram() }}
                </span>
                <span class="font-serif text-lg font-medium tracking-tight">{{ $settings->teacher_name }}</span>
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-2xl px-5 py-16 sm:px-8 sm:py-24">
        <p class="flex items-center gap-3 text-xs font-semibold tracking-[0.22em] text-brass uppercase">
            <span aria-hidden="true" class="h-px w-10 bg-current"></span>
            Depoimento
        </p>

        <h1 class="mt-5 font-serif text-4xl leading-[1.1] font-medium tracking-tight text-balance sm:text-5xl">
            Como estão sendo as suas aulas?
        </h1>

        <p class="mt-5 text-lg leading-relaxed text-pretty text-ink-soft">
            Seu relato ajuda outras pessoas a conhecerem o meu trabalho. Leva só um minuto, e ele só aparece no site depois da minha aprovação.
        </p>

        <div class="mt-12">
            <livewire:testimonial-form />
        </div>
    </main>
</x-layouts.site>
