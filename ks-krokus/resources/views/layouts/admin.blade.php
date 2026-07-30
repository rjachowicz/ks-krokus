<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
    @include('partials.head')
</head>
<body class="admin-body">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a href="{{ route('admin.dashboard') }}" class="admin-brand">
                <span class="admin-brand__mark">KS</span>
                <span>Panel Krokus</span>
            </a>

            <nav class="admin-nav" aria-label="Nawigacja panelu">
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                >
                    Pulpit
                </a>

                @if (auth()->user()->canManageContent())
                    <div class="admin-nav__label">Treści</div>

                    <a
                        href="{{ route('admin.posts.index') }}"
                        class="{{ request()->routeIs('admin.posts.*') ? 'active' : '' }}"
                    >
                        Aktualności
                    </a>

                    <a
                        href="{{ route('admin.events.index') }}"
                        class="{{ request()->routeIs('admin.events.*') ? 'active' : '' }}"
                    >
                        Kalendarz
                    </a>

                    <a
                        href="{{ route('admin.results.index') }}"
                        class="{{ request()->routeIs('admin.results.*') ? 'active' : '' }}"
                    >
                        Wyniki
                    </a>
                @endif

                @if (auth()->user()->isAdmin())
                    <div class="admin-nav__label">Administracja</div>

                    <a
                        href="{{ route('admin.users.index') }}"
                        class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                    >
                        Użytkownicy
                    </a>

                    <a
                        href="{{ route('admin.positions.index') }}"
                        class="{{ request()->routeIs('admin.positions.*') ? 'active' : '' }}"
                    >
                        Funkcje klubowe
                    </a>

                    <a
                        href="{{ route('admin.competitions.index') }}"
                        class="{{ request()->routeIs('admin.competitions.*') ? 'active' : '' }}"
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

        <div class="admin-main">
            <header class="admin-topbar">
                <div>
                    <strong>@yield('admin_title', 'Panel administracyjny')</strong>
                </div>

                <div class="admin-user">
                    <span>
                        <strong>{{ auth()->user()->name }}</strong><br>
                        {{ auth()->user()->role->label() }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary">Wyloguj</button>
                    </form>
                </div>
            </header>

            <main class="admin-content">
                @if (session('success'))
                    <div class="flash flash--success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="flash flash--error">
                        <strong>Formularz zawiera błędy:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
