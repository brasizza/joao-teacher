<footer class="bg-ink-deep text-paper/70">
    <div class="mx-auto grid max-w-6xl gap-10 px-5 py-16 sm:px-8 md:grid-cols-3">
        <div>
            <p class="font-serif text-2xl text-paper">{{ $settings->teacher_name }}</p>
            @if (filled($settings->tagline))
                <p class="mt-2 text-sm">{{ $settings->tagline }}</p>
            @endif
        </div>

        <nav aria-label="Rodapé" class="flex flex-col gap-2 text-sm">
            @foreach ($navigation as $anchor => $label)
                <a href="#{{ $anchor }}" class="w-fit transition hover:text-paper">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex flex-col gap-3 text-sm">
            @if ($whatsAppUrl)
                <a href="{{ $whatsAppUrl }}" target="_blank" rel="noopener" class="inline-flex w-fit items-center gap-2 transition hover:text-paper">
                    <x-site.whatsapp-icon class="size-4" /> WhatsApp
                </a>
            @endif
            @if (filled($settings->email))
                <a href="mailto:{{ $settings->email }}" class="w-fit transition hover:text-paper">{{ $settings->email }}</a>
            @endif
            @if (filled($settings->instagram_url))
                <a href="{{ $settings->instagram_url }}" target="_blank" rel="noopener" class="w-fit transition hover:text-paper">Instagram</a>
            @endif
        </div>
    </div>

    <div class="border-t border-paper/10">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-5 py-6 text-xs text-paper/50 sm:px-8">
            <p>© {{ now()->year }} {{ $settings->teacher_name }}. Todos os direitos reservados.</p>
            <a href="{{ route('filament.admin.pages.dashboard') }}" class="transition hover:text-paper/80">Área do professor</a>
        </div>
    </div>
</footer>
