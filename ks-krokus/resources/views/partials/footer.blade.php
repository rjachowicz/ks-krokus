@php($club = config('club'))

<footer class="site-footer">
    <div class="footer-container">
        <div>
            <strong>{{ $club['name'] }}</strong>
            <p>{{ $club['address']['formatted'] }}</p>
        </div>

        <nav class="footer-links" aria-label="Nawigacja w stopce">
            <a href="{{ route('news.index') }}">Aktualności</a>
            <a href="{{ route('calendar.index') }}">Kalendarz</a>
            <a href="{{ route('results.index') }}">Wyniki</a>
            <a href="{{ route('contact') }}">Kontakt</a>
            <a href="{{ route('rules') }}">Regulamin</a>
            <a href="{{ route('rodo') }}">RODO</a>
            @guest
                <a href="{{ route('login') }}">Logowanie</a>
            @else
                <a href="{{ route('admin.dashboard') }}">Panel</a>
            @endguest
        </nav>

        <p class="footer-copy">© {{ now()->year }} KS Krokus</p>
    </div>
</footer>
