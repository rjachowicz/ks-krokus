@extends('layouts.app')

@section('title', 'KS Krokus — Klub Strzelecki Nowy Sącz')
@section('meta_description', 'Klub Strzelecki Krokus LOK w Nowym Sączu. Treningi, zawody, patent strzelecki, licencja PZSS i członkostwo w klubie.')

@section('content')
    <section class="hero" aria-labelledby="home-title">
        <div class="hero-content">
            <span class="category-tag">Tradycja od 1999 roku</span>
            <h1 id="home-title">Klub Strzelecki <span class="highlight">Krokus</span> Nowy Sącz</h1>
            <p>
                Oficjalny klub zrzeszony w Polskim Związku Strzelectwa Sportowego oraz Małopolskim Związku
                Strzelectwa Sportowego. Szkolimy bezpiecznie, rozwijamy sportową rywalizację i pomagamy członkom
                zdobywać patent oraz licencję zawodniczą.
            </p>

            <div class="btn-group">
                <a href="{{ route('contact') }}" class="btn btn-primary">Dołącz do klubu</a>
                <a href="#historia" class="btn btn-secondary">Poznaj historię</a>
            </div>
        </div>

        <div class="hero-visual" aria-hidden="true">
            <div class="visual-canvas"></div>
            <div class="telemetry-overlay">
                <span>Strzelnica sportowa</span>
                <span>Nowy Sącz</span>
            </div>
        </div>
    </section>

    <section class="hero-stats-bar" aria-label="Najważniejsze informacje o klubie">
        <article class="stat-card">
            <span class="stat-number">25+</span>
            <span class="stat-label">lat tradycji</span>
        </article>
        <article class="stat-card">
            <span class="stat-number">20+</span>
            <span class="stat-label">zawodów rocznie</span>
        </article>
        <article class="stat-card">
            <span class="stat-number">PZSS</span>
            <span class="stat-label">licencja klubowa</span>
        </article>
    </section>

    <section class="features-section" aria-labelledby="about-title">
        <header class="section-header">
            <h2 id="about-title" class="section-title">O Klubie Strzeleckim</h2>
            <span class="section-meta">KS_KROKUS / 01</span>
        </header>

        <div class="bento-grid">
            <article class="card bento-span-2">
                <div>
                    <span class="card-code">SEC_01 // TRENINGI I KOMPETENCJE</span>
                    <h3>Kompleksowe szkolenie strzeleckie</h3>
                    <p>
                        Regularne zajęcia z pistoletu, karabinu i strzelby gładkolufowej pod opieką doświadczonej
                        kadry. Nacisk kładziemy na bezpieczeństwo, poprawną technikę i systematyczny rozwój zawodnika.
                    </p>
                </div>
                <footer class="card-footer">
                    <span class="card-status">TRENINGI KLUBOWE</span>
                    <a href="{{ route('contact') }}#lokalizacje" class="card-link">Sprawdź lokalizacje →</a>
                </footer>
            </article>

            <article class="card">
                <div>
                    <span class="card-code">SEC_02 // UPRAWNIENIA</span>
                    <h3>Patent i licencja PZSS</h3>
                    <p>
                        Przygotowanie do egzaminu na patent strzelecki oraz wsparcie w procedurach związanych
                        z licencją zawodniczą i dalszym rozwojem sportowym.
                    </p>
                </div>
                <footer class="card-footer">
                    <span class="card-status">SZKOLENIE PATENTOWE</span>
                    <a href="{{ route('club') }}#szkolenie-patentowe" class="card-link">Poznaj program →</a>
                </footer>
            </article>

            <article class="card">
                <div>
                    <span class="card-code">SEC_03 // ZAPLECZE</span>
                    <h3>Broń i infrastruktura klubowa</h3>
                    <p>
                        Członkowie korzystają z zaplecza treningowego i broni klubowej zgodnie z regulaminem,
                        uprawnieniami oraz zasadami obowiązującymi na danej osi strzeleckiej.
                    </p>
                </div>
                <footer class="card-footer">
                    <span class="card-status">ZASOBY KLUBOWE</span>
                    <a href="{{ route('contact') }}" class="card-link">Zapytaj zarząd →</a>
                </footer>
            </article>

            <article class="card bento-span-2">
                <div>
                    <span class="card-code">SEC_04 // RYWALIZACJA SPORTOWA</span>
                    <h3>Zawody o randze regionalnej i ogólnopolskiej</h3>
                    <p>
                        Organizujemy zawody wpisane do kalendarza MZSS, w tym Puchar Zimy, Krokus Open oraz
                        Memoriał majora rez. Eugeniusza Nurka. Starty umożliwiają realizację wymaganej liczby
                        konkurencji potrzebnych do przedłużenia licencji zawodniczej.
                    </p>
                </div>
                <footer class="card-footer">
                    <span class="card-status">SEZON SPORTOWY</span>
                    <a href="{{ route('contact') }}" class="card-link">Kontakt w sprawie zawodów →</a>
                </footer>
            </article>
        </div>
    </section>

    <section id="historia" class="features-section" aria-labelledby="history-title">
        <header class="section-header">
            <h2 id="history-title" class="section-title">Historia Klubu Krokus</h2>
            <span class="section-meta">CHRONOLOGY_LOG</span>
        </header>

        <div class="timeline">
            <article class="timeline-item">
                <div class="timeline-year">1999</div>
                <div class="timeline-content">
                    <h3>Inicjatywa i powstanie klubu</h3>
                    <p>
                        Inicjatorem powstania Klubu Strzeleckiego przy LOK w Nowym Sączu był major rez. Eugeniusz
                        Nurek. W czerwcu 1999 roku rozpoczął formalne starania o rejestrację klubu w PZSS.
                    </p>
                </div>
            </article>

            <article class="timeline-item">
                <div class="timeline-year">2000</div>
                <div class="timeline-content">
                    <h3>Oficjalna rejestracja w PZSS</h3>
                    <p>
                        Klub uzyskał pełną rejestrację zawodniczą i licencyjną, otwierając zawodnikom Sądecczyzny
                        drogę do współzawodnictwa na szczeblu wojewódzkim i krajowym.
                    </p>
                </div>
            </article>

            <article class="timeline-item">
                <div class="timeline-year">ROZWÓJ</div>
                <div class="timeline-content">
                    <h3>Współpraca i kultura obronna</h3>
                    <p>
                        Klub wyszkolił setki strzelców, kolekcjonerów i instruktorów, równolegle rozwijając lokalną
                        kulturę obronną oraz edukację z zakresu bezpiecznego posługiwania się bronią.
                    </p>
                </div>
            </article>

            <article class="timeline-item">
                <div class="timeline-year">DZIŚ</div>
                <div class="timeline-content">
                    <h3>Kontynuacja tradycji</h3>
                    <p>
                        Pamięć o założycielu pozostaje żywa dzięki corocznemu Memoriałowi Eugeniusza Nurka.
                        Zarząd i członkowie konsekwentnie rozwijają infrastrukturę oraz poziom wyszkolenia.
                    </p>
                </div>
            </article>
        </div>
    </section>

    <section class="features-section" aria-labelledby="join-title">
        <header class="section-header">
            <h2 id="join-title" class="section-title">Jak dołączyć do Krokusa?</h2>
            <span class="section-meta">JOIN_PROTOCOL</span>
        </header>

        <div class="steps-grid">
            <article class="step-card">
                <div class="step-num">01</div>
                <h3>Pobierz deklarację</h3>
                <p>Wypełnij deklarację członkowską i przygotuj dokumenty wymagane przez klub.</p>
            </article>
            <article class="step-card">
                <div class="step-num">02</div>
                <h3>Skontaktuj się z zarządem</h3>
                <p>Ustal dalszą procedurę, termin spotkania oraz sposób przekazania dokumentów.</p>
            </article>
            <article class="step-card">
                <div class="step-num">03</div>
                <h3>Rozpocznij szkolenie</h3>
                <p>Poznaj zasady bezpieczeństwa, regulamin strzelnicy i podstawy sportu strzeleckiego.</p>
            </article>
            <article class="step-card">
                <div class="step-num">04</div>
                <h3>Zdobądź uprawnienia</h3>
                <p>Przystąp do egzaminu na patent, uzyskaj licencję i rozpocznij starty w zawodach.</p>
            </article>
        </div>
    </section>
@endsection
