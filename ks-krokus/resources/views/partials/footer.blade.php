@php($club = config('club'))

<footer class="site-footer">
    <div class="footer-container">
        <div>
            <strong>{{ $club['name'] }}</strong>
            <p>{{ $club['address']['formatted'] }}</p>
        </div>

        <nav class="footer-links" aria-label="Nawigacja w stopce">
            <a href="{{ route('contact') }}">Kontakt</a>
            <a href="{{ route('rules') }}">Regulamin</a>
            <a href="{{ route('rodo') }}">RODO</a>
            <a href="mailto:{{ $club['email'] }}">{{ $club['email'] }}</a>
        </nav>

        <p class="footer-copy">© {{ now()->year }} KS Krokus</p>
    </div>
</footer>
