<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
    @include('partials.head')
</head>
<body class="admin-body">
    <a class="skip-link" href="#admin-main-content">Przejdź do treści</a>
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

                @if (auth()->user()->canManageContent())
                    <div class="admin-nav__label">Treści</div>

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
                @endif

                @if (auth()->user()->isAdmin())
                    <div class="admin-nav__label">Administracja</div>

                    <a
                        href="{{ route('admin.users.index') }}"
                        class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                        @if (request()->routeIs('admin.users.*')) aria-current="page" @endif
                    >
                        Użytkownicy
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

                <div class="admin-nav__label">Strona</div>

                <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer">
                    Otwórz stronę
                </a>
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

                <div class="admin-user">
                    <span>
                        <strong>{{ auth()->user()->name }}</strong><br>
                        {{ auth()->user()->role->label() }}
                    </span>

                    <button
                        class="admin-theme-toggle"
                        type="button"
                        aria-label="Przełącz motyw kolorystyczny"
                        title="Przełącz motyw"
                        data-theme-toggle
                    >
                        <span aria-hidden="true">◐</span>
                    </button>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary">Wyloguj</button>
                    </form>
                </div>
            </header>

            <main id="admin-main-content" class="admin-content" tabindex="-1">
                <x-form-errors />
                @yield('content')
            </main>
        </div>
    </div>

    <dialog class="confirm-dialog" data-confirm-dialog>
        <form method="dialog">
            <h2>Potwierdź operację</h2>
            <p data-confirm-message></p>
            <div class="confirm-dialog__actions">
                <button type="submit" class="btn btn-secondary">Anuluj</button>
                <button type="button" class="btn btn-danger" data-confirm-accept>Potwierdź</button>
            </div>
        </form>
    </dialog>

    @stack('scripts')
</body>
</html>
