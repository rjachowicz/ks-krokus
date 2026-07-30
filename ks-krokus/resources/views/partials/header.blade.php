<header class="site-header" data-site-header>
    <div class="nav-container">
        <a class="logo" href="{{ route('home') }}" aria-label="KS Krokus — strona główna">
            <span class="logo-mark" aria-hidden="true">KS</span>

            <span>
                <span class="logo-title">KROKUS</span>
                <span class="logo-subtitle">
                    <span class="status-dot" aria-hidden="true"></span>
                    LOK NOWY SĄCZ
                </span>
            </span>
        </a>

        <nav
            id="main-navigation"
            class="main-nav"
            aria-label="Główna nawigacja"
            data-main-navigation
        >
            <ul>
                <li>
                    <a href="{{ route('home') }}"
                       class="{{ request()->routeIs('home') ? 'active' : '' }}"
                       @if (request()->routeIs('home')) aria-current="page" @endif>
                        Start
                    </a>
                </li>
                <li>
                    <a href="{{ route('club') }}"
                       class="{{ request()->routeIs('club') ? 'active' : '' }}"
                       @if (request()->routeIs('club')) aria-current="page" @endif>
                        Klub
                    </a>
                </li>
                <li>
                    <a href="{{ route('contact') }}"
                       class="{{ request()->routeIs('contact') ? 'active' : '' }}"
                       @if (request()->routeIs('contact')) aria-current="page" @endif>
                        Kontakt
                    </a>
                </li>
                <li>
                    <a href="{{ route('rules') }}"
                       class="{{ request()->routeIs('rules') ? 'active' : '' }}"
                       @if (request()->routeIs('rules')) aria-current="page" @endif>
                        Regulamin
                    </a>
                </li>
                <li>
                    <a href="{{ route('rodo') }}"
                       class="{{ request()->routeIs('rodo') ? 'active' : '' }}"
                       @if (request()->routeIs('rodo')) aria-current="page" @endif>
                        RODO
                    </a>
                </li>
            </ul>
        </nav>

        <div class="header-actions">
            <button
                class="icon-button theme-toggle-btn"
                type="button"
                aria-label="Przełącz motyw kolorystyczny"
                title="Przełącz motyw"
                data-theme-toggle
            >
                <svg class="theme-icon sun-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.65 17.65l1.42 1.42M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.65 6.35l1.42-1.42"></path>
                </svg>

                <svg class="theme-icon moon-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5 8.5 8.5 0 1 0 20.5 14.2Z"></path>
                </svg>
            </button>

            <button
                class="mobile-menu-toggle"
                type="button"
                aria-label="Otwórz menu"
                aria-controls="main-navigation"
                aria-expanded="false"
                data-mobile-menu-toggle
            >
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>
    </div>
</header>
