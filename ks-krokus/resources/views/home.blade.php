@extends('layouts.app')

@section('title', 'KS Krokus — Klub Strzelecki Nowy Sącz')
@section('meta_description', 'Klub Strzelecki Krokus LOK w Nowym Sączu. Treningi, zawody, patent strzelecki, licencja PZSS i członkostwo w klubie.')

@php($club = config('club'))

@section('content')
    <x-page-hero
        id="home-title"
        eyebrow="Tradycja od {{ $club['founded_year'] }} roku"
        :visual="true"
        visual-class="visual-canvas--home"
        visual-primary="Strzelnica sportowa"
        visual-secondary="Nowy Sącz"
    >
        <x-slot:title>
            Klub Strzelecki <span class="highlight">Krokus</span> Nowy Sącz
        </x-slot:title>

        <x-slot:description>
            <p>
                Oficjalny klub zrzeszony w Polskim Związku Strzelectwa Sportowego oraz Małopolskim Związku
                Strzelectwa Sportowego. Szkolimy bezpiecznie, rozwijamy sportową rywalizację i pomagamy członkom
                zdobywać patent oraz licencję zawodniczą.
            </p>
        </x-slot:description>

        <x-slot:actions>
            <a href="{{ route('contact') }}" class="btn btn-primary">Dołącz do klubu</a>
            <a href="#historia" class="btn btn-secondary">Poznaj historię</a>
        </x-slot:actions>
    </x-page-hero>

    <section class="hero-stats-bar" aria-label="Najważniejsze informacje o klubie">
        <x-stat-card :value="(now()->year - $club['founded_year']).'+'" label="lat tradycji"/>
        <x-stat-card :value="$club['annual_events'].'+'" label="zawodów rocznie"/>
        <x-stat-card value="PZSS" label="licencja klubowa"/>
    </section>

    <section class="features-section" aria-labelledby="about-title">
        <x-section-heading id="about-title" title="O Klubie Strzeleckim" meta="KS_KROKUS / 01"/>

        <div class="bento-grid">
            <x-content-card
                class="bento-span-2"
                code="SEC_01 // TRENINGI I KOMPETENCJE"
                title="Kompleksowe szkolenie strzeleckie"
                status="TRENINGI KLUBOWE"
                :href="route('contact').'#lokalizacje'"
                link-label="Sprawdź lokalizacje →"
            >
                <p>
                    Regularne zajęcia z pistoletu, karabinu i strzelby gładkolufowej pod opieką doświadczonej
                    kadry. Nacisk kładziemy na bezpieczeństwo, poprawną technikę i systematyczny rozwój zawodnika.
                </p>
            </x-content-card>

            <x-content-card
                code="SEC_02 // UPRAWNIENIA"
                title="Patent i licencja PZSS"
                status="SZKOLENIE PATENTOWE"
                :href="route('club').'#szkolenie-patentowe'"
                link-label="Poznaj program →"
            >
                <p>
                    Przygotowanie do egzaminu na patent strzelecki oraz wsparcie w procedurach związanych
                    z licencją zawodniczą i dalszym rozwojem sportowym.
                </p>
            </x-content-card>

            <x-content-card
                code="SEC_03 // ZAPLECZE"
                title="Broń i infrastruktura klubowa"
                status="ZASOBY KLUBOWE"
                :href="route('contact')"
                link-label="Zapytaj zarząd →"
            >
                <p>
                    Członkowie korzystają z zaplecza treningowego i broni klubowej zgodnie z regulaminem,
                    uprawnieniami oraz zasadami obowiązującymi na danej osi strzeleckiej.
                </p>
            </x-content-card>

            <x-content-card
                class="bento-span-2"
                code="SEC_04 // RYWALIZACJA SPORTOWA"
                title="Zawody o randze regionalnej i ogólnopolskiej"
                status="SEZON SPORTOWY"
                :href="route('contact')"
                link-label="Kontakt w sprawie zawodów →"
            >
                <p>
                    Organizujemy zawody wpisane do kalendarza MZSS, w tym Puchar Zimy, Krokus Open oraz
                    Memoriał majora rez. Eugeniusza Nurka. Starty umożliwiają realizację wymaganej liczby
                    konkurencji potrzebnych do przedłużenia licencji zawodniczej.
                </p>
            </x-content-card>
        </div>
    </section>

    <section id="historia" class="features-section" aria-labelledby="history-title">
        <x-section-heading id="history-title" title="Historia Klubu Krokus" meta="CHRONOLOGY_LOG"/>

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
        <x-section-heading id="join-title" title="Jak dołączyć do Krokusa?" meta="JOIN_PROTOCOL"/>

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
