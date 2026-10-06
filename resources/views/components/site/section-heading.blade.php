@props([
    'number',
    'eyebrow',
    'title',
    'intro' => null,
    'inverted' => false,
])

<header {{ $attributes->merge(['class' => 'max-w-3xl']) }}>
    <p @class([
        'flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.22em]',
        'text-brass' => ! $inverted,
        'text-brass-light' => $inverted,
    ])>
        <span class="font-serif text-sm tracking-normal">{{ $number }}</span>
        <span aria-hidden="true" class="h-px w-10 bg-current"></span>
        <span>{{ $eyebrow }}</span>
    </p>

    <h2 @class([
        'mt-5 font-serif text-4xl font-medium leading-[1.1] tracking-tight text-balance sm:text-5xl',
        'text-ink' => ! $inverted,
        'text-paper' => $inverted,
    ])>
        {{ $title }}
    </h2>

    @if (filled($intro))
        <p @class([
            'mt-5 text-lg leading-relaxed text-pretty',
            'text-ink-soft' => ! $inverted,
            'text-paper/75' => $inverted,
        ])>
            {{ $intro }}
        </p>
    @endif
</header>
