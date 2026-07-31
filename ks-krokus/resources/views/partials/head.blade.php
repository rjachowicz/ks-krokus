<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light dark">
<meta property="csp-nonce" nonce="{{ Vite::cspNonce() }}">
@hasSection('robots')
    <meta name="robots" content="@yield('robots')">
@endif
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

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

<script nonce="{{ Vite::cspNonce() }}">
    (() => {
        document.documentElement.classList.remove('no-js');

        const storageKey = 'ks-krokus-theme';
        let savedTheme = null;

        try {
            savedTheme = localStorage.getItem(storageKey);
        } catch {
            // Tryb prywatny lub polityka przeglądarki może blokować storage.
        }
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
