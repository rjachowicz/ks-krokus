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
                            <x-icon name="account" />
                            Zaloguj się
                        </a>
                    @else
                        <span class="main-nav__account-label">
                            Konto: {{ auth()->user()->name }} · {{ auth()->user()->role->label() }}
                        </span>

                        <a class="main-nav__account-link" href="{{ route('account.show') }}">
                            <x-icon name="account" />
                            Moje konto
                        </a>

                        <a class="main-nav__account-link" href="{{ route('notifications.index') }}">
                            <x-icon name="notification" />
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
                                <x-icon name="logout" />
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
                    <x-icon name="notification" />
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
                        <x-icon name="account" />
                        <span>Moje konto</span>
                        <x-icon name="chevron" class="account-menu__chevron" />
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

                        <a href="{{ route('account.show') }}">
                            <x-icon name="account" />
                            Moje konto
                        </a>
                        <a href="{{ route('notifications.index') }}">
                            <x-icon name="notification" />
                            Powiadomienia
                            @if ($unreadNotificationsCount > 0)
                                <span class="notification-count" aria-label="nieprzeczytane powiadomienia: {{ $unreadNotificationsCount }}">{{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}</span>
                            @endif
                        </a>

                        @if (auth()->user()->canManageContent())
                            <a href="{{ route('admin.dashboard') }}">
                                <x-icon name="home" />
                                Przejdź do panelu administracyjnego
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">
                                <x-icon name="logout" />
                                Wyloguj się
                            </button>
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
                <x-icon name="sun" class="theme-icon sun-icon" />
                <x-icon name="moon" class="theme-icon moon-icon" />
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
