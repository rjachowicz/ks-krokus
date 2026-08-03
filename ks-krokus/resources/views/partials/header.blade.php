<header class="site-header" data-site-header>
    <div class="nav-container page-container">
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
                    <a href="{{ route('news.index') }}"
                       class="{{ request()->routeIs('news.*') ? 'active' : '' }}"
                       @if (request()->routeIs('news.*')) aria-current="page" @endif>
                        Aktualności
                    </a>
                </li>
                <li>
                    <a href="{{ route('calendar.index') }}"
                       class="{{ request()->routeIs('calendar.*') ? 'active' : '' }}"
                       @if (request()->routeIs('calendar.*')) aria-current="page" @endif>
                        Kalendarz
                    </a>
                </li>
                <li>
                    <a href="{{ route('results.index') }}"
                       class="{{ request()->routeIs('results.*') ? 'active' : '' }}"
                       @if (request()->routeIs('results.*')) aria-current="page" @endif>
                        Wyniki
                    </a>
                </li>
                <li>
                    <a href="{{ route('listings.index') }}"
                       class="{{ request()->routeIs('listings.*') ? 'active' : '' }}"
                       @if (request()->routeIs('listings.*')) aria-current="page" @endif>
                        Ogłoszenia
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

                <li class="main-nav__account">
                    @guest
                        <a class="main-nav__account-link" href="{{ route('account-requests.create') }}">
                            Wniosek o konto
                        </a>
                        <a class="main-nav__account-link" href="{{ route('login') }}">
                            <svg class="account-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="8" r="4"></circle>
                                <path d="M4.5 21a7.5 7.5 0 0 1 15 0"></path>
                            </svg>
                            Zaloguj się
                        </a>
                    @else
                        <span class="main-nav__account-label">
                            Konto: {{ auth()->user()->name }} · {{ auth()->user()->role->label() }}
                        </span>

                        <a class="main-nav__account-link" href="{{ route('account.show') }}">
                            <svg class="account-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="8" r="4"></circle>
                                <path d="M4.5 21a7.5 7.5 0 0 1 15 0"></path>
                            </svg>
                            Moje konto
                        </a>

                        <a class="main-nav__account-link" href="{{ route('notifications.index') }}">
                            Powiadomienia
                            @if ($unreadNotificationsCount > 0)
                                <span class="notification-count" aria-label="nieprzeczytane powiadomienia: {{ $unreadNotificationsCount }}">{{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}</span>
                            @endif
                        </a>

                        @if (auth()->user()->canManageContent())
                            <a class="main-nav__account-link" href="{{ route('admin.dashboard') }}">Panel administracyjny</a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="main-nav__logout" type="submit">
                                <svg class="account-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M14 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-3"></path>
                                    <path d="M10 12h11M18 9l3 3-3 3"></path>
                                </svg>
                                Wyloguj się
                            </button>
                        </form>
                    @endguest
                </li>
            </ul>
        </nav>

        <div class="header-actions">
            @guest
                <a class="header-account-request" href="{{ route('account-requests.create') }}">Wniosek o konto</a>
                <a class="header-login" href="{{ route('login') }}">Zaloguj się</a>
            @else
                <a
                    class="header-notifications"
                    href="{{ route('notifications.index') }}"
                    aria-label="Powiadomienia{{ $unreadNotificationsCount > 0 ? ': '.$unreadNotificationsCount.' nieprzeczytanych' : '' }}"
                    title="Powiadomienia"
                >
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
                        <path d="M10 21h4"></path>
                    </svg>
                    @if ($unreadNotificationsCount > 0)
                        <span class="notification-count" aria-hidden="true">{{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}</span>
                    @endif
                </a>

                <div class="account-menu" data-account-menu>
                    <button
                        class="account-menu__trigger"
                        type="button"
                        aria-controls="account-menu-panel"
                        aria-expanded="false"
                        data-account-menu-toggle
                    >
                        <svg class="account-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="8" r="4"></circle>
                            <path d="M4.5 21a7.5 7.5 0 0 1 15 0"></path>
                        </svg>
                        <span>Moje konto</span>
                        <svg class="account-menu__chevron" viewBox="0 0 12 8" aria-hidden="true">
                            <path d="m1 1 5 5 5-5"></path>
                        </svg>
                    </button>

                    <div
                        id="account-menu-panel"
                        class="account-menu__panel"
                        data-account-menu-panel
                        hidden
                    >
                        <p class="account-menu__identity">
                            <strong>{{ auth()->user()->name }}</strong>
                            <span>{{ auth()->user()->role->label() }}</span>
                        </p>

                        <a href="{{ route('account.show') }}">Moje konto</a>
                        <a href="{{ route('notifications.index') }}">
                            Powiadomienia
                            @if ($unreadNotificationsCount > 0)
                                <span class="notification-count" aria-label="nieprzeczytane powiadomienia: {{ $unreadNotificationsCount }}">{{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}</span>
                            @endif
                        </a>

                        @if (auth()->user()->canManageContent())
                            <a href="{{ route('admin.dashboard') }}">Przejdź do panelu administracyjnego</a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">Wyloguj się</button>
                        </form>
                    </div>
                </div>
            @endguest

            <button
                class="icon-button"
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
