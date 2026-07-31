@extends('layouts.app')

@section('title', 'Informacje klubowe — KS Krokus')
@section('meta_description', 'Składki, dokumenty, szkolenia, procedury urzędowe i zasady korzystania ze strzelnicy KS Krokus LOK w Nowym Sączu.')

@php
    $club = config('club');
    $finance = $club['finance'];
    $training = $club['patent_training'];
@endphp

@section('content')
    <x-page-hero id="club-page-title" eyebrow="KOMPENDIUM WIEDZY" class="regulamin-hero">
        <x-slot:title>Informacje <span class="highlight">Klubowe</span></x-slot:title>
        <x-slot:description>
            <p>
                Zasady finansowe, procedury urzędowe, dokumentacja, szkolenia oraz infrastruktura
                {{ $club['full_name'] }}.
            </p>
        </x-slot:description>
    </x-page-hero>

    <section class="features-section features-section--flush" aria-label="Informacje i procedury klubowe">
        <h2 class="sr-only">Informacje i procedury klubowe</h2>
        <div class="bento-grid">
            <x-content-card
                id="finanse"
                class="bento-span-2"
                code="SEC_01 // FINANSE I SKŁADKI"
                title="Składki członkowskie i rachunek bankowy"
                status="TERMIN: {{ $finance['membership_deadline'] }}"
                link-label="KROKUS_FINANCE"
            >
                <p>
                    Prosimy o dokonywanie wpłat składek członkowskich oraz opłat na licencję PZSS
                    na przypisany dla Klubu rachunek bankowy:
                </p>

                <div class="bank-box">
                    <strong>{{ $finance['account_holder'] }}</strong><br>
                    <span class="bank-account">{{ $finance['account_number'] }}</span>
                </div>

                <div class="fees-grid">
                    <div class="fee-box">
                        <strong>Składka podstawowa:</strong>
                        <ul class="plain-list">
                            <li>Mężczyźni: <strong>{{ number_format($finance['fees']['membership']['men'], 0, ',', ' ') }} zł</strong></li>
                            <li>Kobiety: <strong>{{ number_format($finance['fees']['membership']['women'], 0, ',', ' ') }} zł</strong></li>
                        </ul>
                    </div>

                    <div class="fee-box">
                        <strong>Składka + Licencja PZSS ({{ $finance['license_year'] }}):</strong>
                        <ul class="plain-list">
                            <li>
                                Mężczyźni:
                                <strong>{{ number_format($finance['fees']['membership_with_license']['men'], 0, ',', ' ') }} zł</strong>
                                (składka + {{ $finance['license_fee'] }} zł lic.)
                            </li>
                            <li>
                                Kobiety:
                                <strong>{{ number_format($finance['fees']['membership_with_license']['women'], 0, ',', ' ') }} zł</strong>
                                (składka + {{ $finance['license_fee'] }} zł lic.)
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="club-info-list">
                    <p><strong>1) Termin płatności:</strong> do <strong>{{ $finance['membership_deadline'] }} r.</strong></p>
                    <p><strong>2) Opis przelewu (składka):</strong> <code>{{ $finance['transfer_titles']['membership'] }}</code></p>
                    <p><strong>3)</strong> Składka wpisowa dla nowych członków: <strong>{{ number_format($finance['fees']['entry_new_member'], 0, ',', ' ') }} zł</strong></p>
                    <p><strong>4)</strong> Składka wpisowa przy przeniesieniu z innego klubu: <strong>{{ number_format($finance['fees']['entry_transfer'], 0, ',', ' ') }} zł</strong></p>
                    <p><strong>5) Opis przelewu (wpisowe):</strong> <code>{{ $finance['transfer_titles']['entry'] }}</code></p>
                </div>
            </x-content-card>

            <x-content-card
                id="dokumenty"
                class="card-highlight-download"
                code="SEC_02 // DOKUMENTACJA"
                badge="PLIK DO POBRANIA"
                title="Deklaracja członkowska"
                footer-class="card-footer-stack"
            >
                <p>
                    Wstąpienie do K.S. Krokus jest możliwe po złożeniu podpisanej deklaracji członkowskiej
                    Zarządowi Klubu oraz opłaceniu składki wpisowej i członkowskiej na konto Klubu.
                </p>

                <x-slot:footer>
                    <span class="card-status">FORMAT PDF</span>
                    <a href="{{ asset($club['documents']['membership_declaration']) }}"
                       class="btn-action"
                       target="_blank"
                       rel="noopener noreferrer"
                       aria-label="Pobierz deklarację członkowską PDF — otwiera w nowej karcie">
                        POBIERZ DEKLARACJĘ ↗
                    </a>
                </x-slot:footer>
            </x-content-card>

            <x-content-card
                id="szkolenie-patentowe"
                code="SEC_03 // SZKOLENIE PATENTOWE"
                badge="NOWY REGULAMIN"
                title="Szkolenie do egzaminu na patent"
                footer-class="card-footer-stack"
            >
                <p>
                    Zgodnie z regulaminem PZSS (od {{ $training['effective_from'] }} r.),
                    Zarząd KS KROKUS prowadzi szkolenia dla członków zainteresowanych patentem.
                </p>

                <ul class="plain-list">
                    <li>
                        <strong>Program:</strong>
                        {{ $training['hours'] }} godz. lekcyjne
                        (w tym {{ $training['practice_hours'] }}h praktyki z bronią).
                    </li>
                    <li>
                        <strong>Koszt:</strong>
                        {{ number_format($training['price'], 0, ',', ' ') }} zł
                        (instruktor, amunicja, broń i strzelnica). Opłata niezależna od wpisowego.
                    </li>
                </ul>

                <x-slot:footer>
                    <span class="card-status">WYTYCZNE PZSS</span>
                    <a href="{{ $training['regulations_url'] }}"
                       class="btn-action"
                       target="_blank"
                       rel="noopener noreferrer"
                       aria-label="Regulamin PZSS — otwiera w nowej karcie">
                        REGULAMIN PZSS ↗
                    </a>
                </x-slot:footer>
            </x-content-card>


            @if ($trainers->isNotEmpty())
                <x-content-card
                    class="bento-full"
                    code="SEC_03A // KADRA"
                    title="Trenerzy i instruktorzy"
                    status="{{ $trainers->count() }} OSÓB"
                    :href="route('contact')"
                    link-label="Pełne dane kontaktowe →"
                >
                    <p>
                        Kadra prowadząca szkolenia i przygotowanie do egzaminu na patent strzelecki.
                    </p>

                    <div class="trainers-grid">
                        @foreach ($trainers as $trainer)
                            <div class="trainer-card">
                                <strong class="trainer-name">{{ $trainer->name }}</strong>

                                @if ($trainer->show_phone_publicly && $trainer->phone)
                                    <a href="tel:{{ $trainer->phone }}" class="trainer-phone">
                                        {{ $trainer->phone }}
                                    </a>
                                @endif

                                @if ($trainer->trainer_bio)
                                    <p>{{ $trainer->trainer_bio }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-content-card>
            @endif

            <x-content-card
                id="refundacja"
                class="bento-span-2 card-highlight-download"
                code="SEC_04 // SPORT I REFUNDACJA"
                badge="WNIOSEK DO POBRANIA"
                title="Zwrot kosztów za zawody"
                footer-class="card-footer-stack"
            >
                <p>Klub na wniosek zwraca członkom koszt startowego po spełnieniu kryteriów regulaminowych.</p>

                <h4 class="club-subtitle">Wymogi konieczne do spełnienia:</h4>
                <ul class="club-list-small">
                    <li>
                        Brak zaległości finansowych względem Klubu na dany rok oraz aktualna licencja zawodnicza.
                    </li>
                    <li>
                        Delegowanie przez Klub do udziału w zawodach z kalendarza PZSS lub MZSS
                        (mistrzostwa Polski / pucharu Polski).
                    </li>
                    <li>
                        Przedstawienie wydatków, komunikatu potwierdzającego udział oraz złożenie wniosku
                        do <strong>30 dni</strong> od zakończenia zawodów.
                    </li>
                </ul>

                <div class="invoice-box">
                    <strong>Dane do faktury:</strong><br>
                    {{ $club['invoice']['name'] }}<br>
                    <em>({{ $club['invoice']['short_name'] }})</em>,
                    {{ $club['invoice']['address'] }}<br>
                    <strong>NIP: {{ $club['invoice']['nip'] }}</strong>
                </div>

                <x-slot:footer>
                    <span class="card-status">REFUNDACJA STARTOWEGO</span>
                    <a href="{{ asset($club['documents']['refund_application']) }}"
                       class="btn-action"
                       target="_blank"
                       rel="noopener noreferrer"
                       aria-label="Pobierz wniosek o zwrot PDF — otwiera w nowej karcie">
                        POBIERZ WNIOSEK O ZWROT ↗
                    </a>
                </x-slot:footer>
            </x-content-card>

            <x-content-card
                id="pozwolenie"
                class="bento-span-2 card-highlight-download"
                code="SEC_05 // PROCEDURY URZĘDOWE"
                badge="UCHWAŁA DO POBRANIA"
                title="Pozwolenie na broń – zaświadczenia klubowe"
                footer-class="card-footer-actions"
            >
                <p>
                    Członkowie mogą wystąpić do Komendanta Wojewódzkiego Policji z wnioskiem o wydanie decyzji
                    uprawniającej do posiadania broni do celów sportowych i kolekcjonerskich.
                </p>

                <h4 class="club-subtitle">Procedura wydawania zaświadczeń:</h4>
                <ol class="club-steps">
                    <li>
                        Prześlij zapytanie na
                        <a href="mailto:{{ $club['email'] }}" class="club-link">{{ $club['email'] }}</a>
                        (podaj imię, nazwisko, rodzaj zaświadczeń oraz informację, czy to pierwsza decyzja,
                        czy rozszerzenie).
                    </li>
                    <li>
                        Zaświadczenia są podpisywane podpisem zaufanym, wysyłane zwrotnie na e-mail
                        oraz bezpośrednio do WPA do <strong>1 dnia roboczego</strong>.
                    </li>
                    <li>
                        Wydrukuj zaświadczenie i dołącz do wniosku wraz z pobraną uchwałą o kolekcjonerstwie.
                    </li>
                </ol>

                <div class="wpa-box">
                    <strong>{{ $club['firearm_permit']['office']['name'] }}</strong><br>
                    {{ $club['firearm_permit']['office']['address'] }} |
                    Tel: {{ implode('; ', $club['firearm_permit']['office']['phones']) }}<br>
                    <strong>Godziny przyjęć:</strong> {{ $club['firearm_permit']['office']['hours'] }}
                </div>

                <x-slot:footer>
                    @if (is_file(public_path($club['documents']['collectors_resolution'])))
                        <a href="{{ asset($club['documents']['collectors_resolution']) }}"
                           class="btn-action"
                           target="_blank"
                           rel="noopener noreferrer"
                           aria-label="Pobierz uchwałę kolekcjonerską PDF — otwiera w nowej karcie">
                            POBIERZ UCHWAŁĘ KOLEKCJONERSKĄ ↗
                        </a>
                    @endif

                    <a href="{{ $club['firearm_permit']['wpa_url'] }}"
                       class="btn-action"
                       target="_blank"
                       rel="noopener noreferrer"
                       aria-label="Strona WPA Kraków — otwiera w nowej karcie">
                        STRONA WPA KRAKÓW ↗
                    </a>
                </x-slot:footer>
            </x-content-card>

            <x-content-card
                code="SEC_06 // SPOŁECZNOŚĆ"
                title="WhatsApp – Społeczność Krokus"
                status="DOBROWOLNA"
                link-label="GRUPY TEMATYCZNE"
            >
                <p>
                    Zachęcamy do dołączenia do społeczności K.S. Krokus LOK Nowy Sącz
                    na platformie WhatsApp. Rejestracja jest dobrowolna.
                </p>

                <p class="whatsapp-subtext">
                    Zgłoszenia prosimy kierować na
                    <a href="mailto:{{ $club['email'] }}" class="club-link">{{ $club['email'] }}</a>
                    lub do członków społeczności.
                </p>

                <p class="whatsapp-disclaimer">
                    Oficjalne wydarzenia (zawody, szkolenia) publikowane są na stronie internetowej.
                </p>
            </x-content-card>

            <x-content-card
                id="dostep-do-strzelnicy"
                class="bento-full"
                code="SEC_07 // INFRASTRUKTURA"
                title="Elektroniczny dostęp do strzelnicy"
                status="KONTROLA DOSTĘPU"
                link-label="UL. TARNOWSKA"
            >
                <p>
                    Strzelnica przy ul. Tarnowskiej w Nowym Sączu objęta jest systemem kontroli dostępu.
                    Karty (koszt {{ $club['range_access']['card_cost'] }} zł) wydaje wiceprezes
                    <strong>{{ $club['range_access']['issuer'] }}</strong> po spełnieniu warunków:
                </p>

                <ul class="plain-list">
                    @foreach ($club['range_access']['requirements'] as $requirement)
                        <li>{{ $requirement }}</li>
                    @endforeach
                </ul>

                <div class="fees-grid">
                    <div class="fee-box">
                        <strong>Przed treningiem:</strong>
                        <ul class="club-list-small">
                            @foreach ($club['range_access']['before_training'] as $rule)
                                <li>{{ $rule }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="fee-box">
                        <strong>Po treningu:</strong>
                        <ul class="club-list-small">
                            @foreach ($club['range_access']['after_training'] as $rule)
                                <li>{{ $rule }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <h4 class="club-subtitle">Osoby uprawnione z dostępem do strzelnicy:</h4>

                <div class="persons-grid">
                    @forelse ($rangeAccessPeople as $person)
                        <div class="person-tile">
                            <span title="{{ $person->name }}">{{ $person->name }}</span>

                            @if ($person->show_phone_publicly && $person->phone)
                                <a href="tel:{{ $person->phone }}">{{ $person->phone }}</a>
                            @else
                                <span>kontakt przez zarząd</span>
                            @endif
                        </div>
                    @empty
                        <p>Lista osób z dostępem nie została jeszcze uzupełniona.</p>
                    @endforelse
                </div>
            </x-content-card>
        </div>
    </section>
@endsection
