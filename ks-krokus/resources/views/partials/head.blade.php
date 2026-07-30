<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light dark">
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $pageTitle = trim($__env->yieldContent(
        'title',
        'KS Krokus — Klub Strzelecki Nowy Sącz',
    ));
    $pageDescription = trim($__env->yieldContent(
        'meta_description',
        'Klub Strzelecki Krokus LOK w Nowym Sączu — treningi, zawody, patent i licencja PZSS.',
    ));
@endphp

<meta name="description" content="{{ $pageDescription }}">
<title>{{ $pageTitle }}</title>

<script>
    (() => {
        const storageKey = 'ks-krokus-theme';
        const savedTheme = localStorage.getItem(storageKey);
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';

        document.documentElement.dataset.theme =
            savedTheme === 'light' || savedTheme === 'dark'
                ? savedTheme
                : systemTheme;
    })();
</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap"
    rel="stylesheet"
>

@vite(['resources/css/app.css', 'resources/js/app.js'])

@stack('styles')
@stack('head')
