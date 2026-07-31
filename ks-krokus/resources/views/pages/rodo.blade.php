@extends('layouts.app')

@section('title', 'Klauzula informacyjna RODO — KS Krokus')
@section('meta_description', 'Klauzula informacyjna RODO dotycząca rejestracji i udziału w zawodach strzeleckich organizowanych przez KS Krokus.')

@php($club = config('club'))

@section('content')
    <x-page-hero id="rodo-page-title" eyebrow="OCHRONA DANYCH OSOBOWYCH" class="regulamin-hero">
        <x-slot:title>
            Klauzula <span class="highlight">RODO</span>
        </x-slot:title>

        <x-slot:description>
            <p>
                Informacja o przetwarzaniu danych osobowych uczestników zawodów
                strzeleckich organizowanych przez KS Krokus.
            </p>
        </x-slot:description>
    </x-page-hero>

    <section class="features-section features-section--flush" aria-label="Treść klauzuli RODO">
        <article class="card regulamin-card">
            <span class="card-code">[ DOC_ID: RODO-2026 ]</span>

            <div class="regulamin-header">
                <h2>KLAUZULA INFORMACYJNA RODO</h2>
                <p class="regulamin-subtitle">
                    Rejestracja zawodników na zawody strzeleckie
                </p>
                <span class="regulamin-date">
                    Zgodnie z art. 13 ust. 1 i 2 Rozporządzenia Parlamentu
                    Europejskiego i Rady (UE) 2016/679
                </span>
            </div>

            <div class="regulamin-body">
                <section class="rodo-section">
                    <h3 class="regulamin-chapter-title">
                        1. Administrator danych
                    </h3>

                    <p>
                        Administratorem danych osobowych uczestników zawodów jest:
                    </p>

                    <address class="rodo-address">
                        <strong>{{ $club['full_name'] }}</strong>
                        <span>{{ $club['address']['formatted'] }}</span>
                        <span>
                            E-mail:
                            <a href="mailto:{{ $club['email'] }}">
                                {{ $club['email'] }}
                            </a>
                        </span>
                    </address>
                </section>

                <section class="rodo-section">
                    <h3 class="regulamin-chapter-title">
                        2. Inspektor Ochrony Danych
                    </h3>

                    <p>
                        Administrator nie powołał Inspektora Ochrony Danych.
                        W sprawach dotyczących ochrony danych osobowych można
                        kontaktować się korespondencyjnie na adres administratora.
                    </p>
                </section>

                <section class="rodo-section">
                    <h3 class="regulamin-chapter-title">
                        3. Cele i podstawy przetwarzania danych
                    </h3>

                    <p>
                        Dane osobowe przetwarzane są w następujących celach:
                    </p>

                    <ol class="rodo-list">
                        <li>
                            Rejestracja i udział w zawodach strzeleckich –
                            art. 6 ust. 1 lit. b RODO.
                        </li>
                        <li>
                            Organizacja i przeprowadzenie zawodów, w tym
                            weryfikacja uprawnień zawodnika oraz zapewnienie
                            bezpieczeństwa – art. 6 ust. 1 lit. f RODO.
                        </li>
                        <li>
                            Wypełnienie obowiązków prawnych ciążących na
                            organizatorze – art. 6 ust. 1 lit. c RODO.
                        </li>
                        <li>
                            Publikacja wyników zawodów obejmująca imię,
                            nazwisko, klub oraz wyniki sportowe –
                            art. 6 ust. 1 lit. f RODO.
                        </li>
                    </ol>

                    <p class="rodo-note">
                        Organizator nie przetwarza wizerunku zawodników
                        na podstawie zgody.
                    </p>
                </section>

                <section class="rodo-section">
                    <h3 class="regulamin-chapter-title">
                        4. Zakres przetwarzanych danych
                    </h3>

                    <p>
                        Przetwarzane są dane niezbędne do rejestracji
                        i udziału w zawodach:
                    </p>

                    <ol class="rodo-list">
                        <li>imię i nazwisko,</li>
                        <li>data urodzenia,</li>
                        <li>numer licencji lub pozwolenia,</li>
                        <li>przynależność klubowa,</li>
                        <li>dane kontaktowe,</li>
                        <li>konkurencje i kategoria startowa,</li>
                        <li>wyniki osiągnięte podczas zawodów.</li>
                    </ol>
                </section>

                <section class="rodo-section">
                    <h3 class="regulamin-chapter-title">
                        5. Odbiorcy danych
                    </h3>

                    <ol class="rodo-list">
                        <li>sędziowie i obsługa zawodów,</li>
                        <li>
                            podmioty wspierające obsługę techniczną zawodów,
                        </li>
                        <li>
                            organy uprawnione na podstawie przepisów prawa.
                        </li>
                    </ol>

                    <p class="rodo-note">
                        Dane nie są przekazywane poza Europejski Obszar
                        Gospodarczy.
                    </p>
                </section>

                <section class="rodo-section">
                    <h3 class="regulamin-chapter-title">
                        6. Okres przechowywania danych
                    </h3>

                    <ol class="rodo-list">
                        <li>
                            Dane rejestracyjne – przez okres niezbędny do
                            realizacji zawodów oraz okres przedawnienia roszczeń.
                        </li>
                        <li>
                            Wyniki zawodów – przez czas wymagany do
                            dokumentowania działalności sportowej.
                        </li>
                    </ol>
                </section>

                <section class="rodo-section">
                    <h3 class="regulamin-chapter-title">
                        7. Prawa osoby, której dane dotyczą
                    </h3>

                    <ol class="rodo-list">
                        <li>dostęp do danych,</li>
                        <li>sprostowanie danych,</li>
                        <li>usunięcie danych,</li>
                        <li>ograniczenie przetwarzania,</li>
                        <li>przenoszenie danych,</li>
                        <li>wniesienie sprzeciwu,</li>
                        <li>wniesienie skargi do Prezesa UODO.</li>
                    </ol>
                </section>

                <section class="rodo-section">
                    <h3 class="regulamin-chapter-title">
                        8. Dobrowolność podania danych
                    </h3>

                    <p>
                        Podanie danych jest dobrowolne, jednak niezbędne
                        do udziału w zawodach. Brak ich podania uniemożliwi
                        rejestrację i start.
                    </p>
                </section>
            </div>

            <footer class="card-footer regulamin-footer">
                <span class="card-status">STATUS: OBOWIĄZUJĄCY</span>
                <a href="{{ route('home') }}" class="card-link">
                    ← Powrót do strony głównej
                </a>
            </footer>
        </article>
    </section>
@endsection
