@props([
    'title',
    'description' => null,
    'image' => null,
    'monogram' => 'J',
    'indexable' => true,
])

<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#1e2a44">

        <title>{{ $title }}</title>

        @unless ($indexable)
            <meta name="robots" content="noindex, nofollow">
        @endunless

        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif

        <link rel="canonical" href="{{ url('/') }}">
        <meta property="og:type" content="website">
        <meta property="og:locale" content="pt_BR">
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:title" content="{{ $title }}">
        @if ($description)
            <meta property="og:description" content="{{ $description }}">
        @endif
        @if ($image)
            <meta property="og:image" content="{{ $image }}">
        @endif
        <meta name="twitter:card" content="summary_large_image">

        <link rel="icon" href="data:image/svg+xml,{{ rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="14" fill="#1e2a44"/><text x="32" y="44" font-family="Georgia,serif" font-size="34" text-anchor="middle" fill="#c4a46e">'.e($monogram).'</text></svg>') }}">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body {{ $attributes->merge(['class' => 'min-h-screen']) }}>
        {{ $slot }}
    </body>
</html>
