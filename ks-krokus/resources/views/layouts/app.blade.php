<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="description"
          content="@yield('meta_description', 'Klub Strzelecki Krokus LOK w Nowym Sączu — treningi, zawody, patent i licencja PZSS.')">

    <title>@yield('title', 'KS Krokus — Klub Strzelecki Nowy Sącz')</title>

    <script>
        (() => {
            const storageKey = 'ks-krokus-theme';
            const savedTheme = localStorage.getItem(storageKey);
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.dataset.theme = savedTheme ?? systemTheme;
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body>
<a class="skip-link" href="#main-content">Przejdź do treści</a>

<header class="site-header" data-site-header>
    <div class="nav-container">
        <a class="logo" href="{{ route('home') }}" aria-label="KS Krokus — strona główna">
            <span class="logo-mark" aria-hidden="true">KS</span>
            <span>
                <span class="logo-title">KROKUS</span>
                <span class="logo-subtitle"><span class="status-dot"></span> LOK NOWY SĄCZ</span>
            </span>
        </a>

        <nav id="mainNav" class="main-nav" aria-label="Główna nawigacja">
            <ul>
                <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"
                       @if(request()->routeIs('home')) aria-current="page" @endif>Start</a></li>
                <li><a href="{{ route('club') }}" class="{{ request()->routeIs('club') ? 'active' : '' }}"
                       @if(request()->routeIs('club')) aria-current="page" @endif>Klub</a></li>
                <li><a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}"
                       @if(request()->routeIs('contact')) aria-current="page" @endif>Kontakt</a></li>
                <li><a href="{{ route('rules') }}" class="{{ request()->routeIs('rules') ? 'active' : '' }}"
                       @if(request()->routeIs('rules')) aria-current="page" @endif>Regulamin</a></li>
                <li><a href="{{ route('rodo') }}" class="{{ request()->routeIs('rodo') ? 'active' : '' }}"
                       @if(request()->routeIs('rodo')) aria-current="page" @endif>RODO</a></li>
            </ul>
        </nav>

        <div class="header-actions">
            <button
                id="themeToggleBtn"
                class="icon-button theme-toggle-btn"
                type="button"
                aria-label="Przełącz motyw kolorystyczny"
                title="Przełącz motyw"
            >
                <svg class="theme-icon sun-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path
                        d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.65 17.65l1.42 1.42M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.65 6.35l1.42-1.42"></path>
                </svg>
                <svg class="theme-icon moon-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5 8.5 8.5 0 1 0 20.5 14.2Z"></path>
                </svg>
            </button>

            <button
                id="mobileMenuBtn"
                class="mobile-menu-toggle"
                type="button"
                aria-label="Otwórz menu"
                aria-controls="mainNav"
                aria-expanded="false"
            >
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>
    </div>
</header>

<main id="main-content">
    @yield('content')
</main>

<footer class="site-footer">
    <div class="footer-container">
        <div>
            <strong>Klub Strzelecki „KROKUS” LOK</strong>
            <p>ul. Tarnowska 32, 33-300 Nowy Sącz</p>
        </div>

        <nav class="footer-links" aria-label="Nawigacja w stopce">
            <a href="{{ route('contact') }}">Kontakt</a>
            <a href="{{ route('rules') }}">Regulamin</a>
            <a href="{{ route('rodo') }}">RODO</a>
            <a href="mailto:zarzad@ks-krokus.pl">zarzad@ks-krokus.pl</a>
        </nav>

        <p class="footer-copy">© <span data-current-year>{{ date('Y') }}</span> KS Krokus</p>
    </div>
</footer>

<script>
    (() => {
        const root = document.documentElement;
        const storageKey = 'ks-krokus-theme';
        const themeButton = document.getElementById('themeToggleBtn');
        const menuButton = document.getElementById('mobileMenuBtn');
        const navigation = document.getElementById('mainNav');
        const header = document.querySelector('[data-site-header]');

        const setTheme = (theme) => {
            root.dataset.theme = theme;
            localStorage.setItem(storageKey, theme);
            themeButton?.setAttribute(
                'aria-label',
                theme === 'dark' ? 'Włącz jasny motyw' : 'Włącz ciemny motyw'
            );
        };

        const closeMenu = () => {
            navigation?.classList.remove('is-open');
            menuButton?.classList.remove('is-open');
            menuButton?.setAttribute('aria-expanded', 'false');
            menuButton?.setAttribute('aria-label', 'Otwórz menu');
        };

        themeButton?.addEventListener('click', () => {
            setTheme(root.dataset.theme === 'dark' ? 'light' : 'dark');
        });

        menuButton?.addEventListener('click', () => {
            const isOpen = navigation?.classList.toggle('is-open') ?? false;
            menuButton.classList.toggle('is-open', isOpen);
            menuButton.setAttribute('aria-expanded', String(isOpen));
            menuButton.setAttribute('aria-label', isOpen ? 'Zamknij menu' : 'Otwórz menu');
        });

        navigation?.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', closeMenu);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 960) {
                closeMenu();
            }
        });

        const updateHeader = () => {
            header?.classList.toggle('is-scrolled', window.scrollY > 12);
        };

        setTheme(root.dataset.theme || 'light');
        updateHeader();
        window.addEventListener('scroll', updateHeader, {passive: true});
    })();
</script>

@stack('scripts')
</body>
</html>
