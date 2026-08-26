<!DOCTYPE html>
<html lang="pl" data-theme="light" class="no-js">
<head>
    @section('robots', 'noindex, nofollow, noarchive')
    @include('partials.head')
</head>
<body class="admin-body">
    <a class="skip-link" href="#admin-main-content" data-admin-skip-link>Przejdź do treści</a>
    @include('partials.toasts')

    <div class="admin-shell">
        <aside id="admin-sidebar" class="admin-sidebar" data-admin-sidebar>
            <a href="{{ route('admin.dashboard') }}" class="admin-brand">
                <span class="admin-brand__mark">KS</span>
                <span>Panel Krokus</span>
            </a>

            <nav class="admin-nav" aria-label="Nawigacja panelu">
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif
                >
                    Pulpit
                </a>

                <a
                    href="{{ route('admin.account.show') }}"
                    class="{{ request()->routeIs('admin.account.*') ? 'active' : '' }}"
                    @if (request()->routeIs('admin.account.*')) aria-current="page" @endif
                >
                    Moje konto
                </a>

                <a
                    href="{{ route('admin.my-listings.index') }}"
                    class="{{ request()->routeIs('admin.my-listings.*') ? 'active' : '' }}"
                    @if (request()->routeIs('admin.my-listings.*')) aria-current="page" @endif
                >
                    Moje ogłoszenia
                </a>

                @if (auth()->user()->canManageContent())
                    <span class="admin-nav__label">Treści</span>

                    <a
                        href="{{ route('admin.posts.index') }}"
                        class="{{ request()->routeIs('admin.posts.*') ? 'active' : '' }}"
                        @if (request()->routeIs('admin.posts.*')) aria-current="page" @endif
                    >
                        Aktualności
                    </a>

                    <a
                        href="{{ route('admin.events.index') }}"
                        class="{{ request()->routeIs('admin.events.*') ? 'active' : '' }}"
                        @if (request()->routeIs('admin.events.*')) aria-current="page" @endif
                    >
                        Kalendarz
                    </a>

                    <a
                        href="{{ route('admin.results.index') }}"
                        class="{{ request()->routeIs('admin.results.*') ? 'active' : '' }}"
                        @if (request()->routeIs('admin.results.*')) aria-current="page" @endif
                    >
                        Wyniki
                    </a>

                    <a
                        href="{{ route('admin.sale-listings.index') }}"
                        class="{{ request()->routeIs('admin.sale-listings.*') ? 'active' : '' }}"
                        @if (request()->routeIs('admin.sale-listings.*')) aria-current="page" @endif
                    >
                        Ogłoszenia
                    </a>
                @endif

                @if (auth()->user()->isAdmin())
                    <span class="admin-nav__label">Administracja</span>

                    <a
                        href="{{ route('admin.users.index') }}"
                        class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                        @if (request()->routeIs('admin.users.*')) aria-current="page" @endif
                    >
                        Użytkownicy
                    </a>

                    <a
                        href="{{ route('admin.account-requests.index') }}"
                        class="{{ request()->routeIs('admin.account-requests.*') ? 'active' : '' }}"
                        @if (request()->routeIs('admin.account-requests.*')) aria-current="page" @endif
                    >
                        Wnioski o konto
                        @if ($pendingAccountRequestsCount > 0)
                            <span class="admin-nav__count" aria-label="oczekujące wnioski: {{ $pendingAccountRequestsCount }}">{{ $pendingAccountRequestsCount }}</span>
                        @endif
                    </a>

                    <a
                        href="{{ route('admin.positions.index') }}"
                        class="{{ request()->routeIs('admin.positions.*') ? 'active' : '' }}"
                        @if (request()->routeIs('admin.positions.*')) aria-current="page" @endif
                    >
                        Funkcje klubowe
                    </a>

                    <a
                        href="{{ route('admin.competitions.index') }}"
                        class="{{ request()->routeIs('admin.competitions.*') ? 'active' : '' }}"
                        @if (request()->routeIs('admin.competitions.*')) aria-current="page" @endif
                    >
                        Konkurencje
                    </a>
                @endif

            </nav>
        </aside>
        <button class="admin-sidebar-backdrop" type="button" aria-label="Zamknij menu panelu" data-admin-menu-backdrop></button>

        <div class="admin-main">
            <header class="admin-topbar">
                <div class="admin-topbar__title">
                    <button
                        type="button"
                        class="admin-menu-toggle"
                        aria-label="Otwórz menu panelu"
                        aria-controls="admin-sidebar"
                        aria-expanded="false"
                        data-admin-menu-toggle
                    >
                        <span></span><span></span><span></span>
                    </button>
                    <strong>@yield('admin_title', 'Panel administracyjny')</strong>
                </div>

                <div class="admin-topbar__actions" data-admin-user>
                    <button
                        class="admin-topbar__control admin-theme-toggle"
                        type="button"
                        aria-label="Przełącz motyw kolorystyczny"
                        title="Przełącz motyw"
                        data-theme-toggle
                    >
                        <x-icon name="sun" class="theme-icon sun-icon" />
                        <x-icon name="moon" class="theme-icon moon-icon" />
                    </button>

                    <div class="admin-account-menu" data-account-menu>
                        <button
                            class="admin-account-menu__trigger"
                            type="button"
                            aria-controls="admin-account-menu-panel"
                            aria-expanded="false"
                            data-account-menu-toggle
                        >
                            <x-icon name="account" />
                            <span class="admin-account-menu__trigger-label">Moje konto</span>
                            <x-icon name="chevron" class="admin-account-menu__chevron" />
                        </button>

                        <div
                            id="admin-account-menu-panel"
                            class="admin-account-menu__panel"
                            data-account-menu-panel
                            hidden
                        >
                            <p class="admin-account-menu__identity">
                                <strong>{{ auth()->user()->name }}</strong>
                                <span>{{ auth()->user()->role->label() }}</span>
                            </p>

                            <a
                                href="{{ route('admin.account.show') }}"
                                @if (request()->routeIs('admin.account.*')) aria-current="page" @endif
                            >
                                <x-icon name="account" />
                                Moje konto
                            </a>

                            <a
                                href="{{ route('notifications.index') }}"
                                aria-label="Powiadomienia{{ $unreadNotificationsCount > 0 ? ': '.$unreadNotificationsCount.' nieprzeczytanych' : '' }}"
                            >
                                <x-icon name="notification" />
                                <span>Powiadomienia</span>
                                @if ($unreadNotificationsCount > 0)
                                    <span class="notification-count" aria-hidden="true">{{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}</span>
                                @endif
                            </a>

                            <a href="{{ route('home') }}">
                                <x-icon name="home" />
                                Otwórz stronę
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit">
                                    <x-icon name="logout" />
                                    Wyloguj się
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main id="admin-main-content" class="admin-content page-container" tabindex="-1" data-admin-content>
                <x-form-errors />
                @yield('content')
            </main>
        </div>
    </div>

    <x-confirm-dialog />

</body>
</html>
