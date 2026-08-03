@extends('layouts.app')

@section('title', 'Kontakt — KS Krokus Nowy Sącz')
@section('meta_description', 'Dane kontaktowe, władze klubu, trenerzy i lokalizacje Klubu Strzeleckiego Krokus LOK w Nowym Sączu.')

@php($club = config('club'))

@section('content')
    <x-page-hero
        id="contact-title"
        eyebrow="Centrala i władze klubu"
        :visual="true"
        visual-class="visual-canvas--contact"
        visual-primary="Sekretariat klubu"
        visual-secondary="Nowy Sącz / Tylicz"
    >
        <x-slot:title>
            Skontaktuj się z <span class="highlight">Krokusem</span>
        </x-slot:title>

        <x-slot:description>
            <p>
                Masz pytania dotyczące członkostwa, treningów, patentu strzeleckiego lub organizacji zawodów?
                Napisz do zarządu albo skontaktuj się bezpośrednio z właściwą osobą.
            </p>
        </x-slot:description>

        <x-slot:actions>
            <a href="mailto:{{ $club['email'] }}" class="btn btn-primary">Napisz e-mail</a>
            <a href="#lokalizacje" class="btn btn-secondary">Zobacz lokalizacje</a>
        </x-slot:actions>
    </x-page-hero>

    <section class="hero-stats-bar page-container ui-grid ui-grid--3" aria-label="Organizacje i kanały kontaktu">
        <x-stat-card value="LOK" label="Liga Obrony Kraju" />
        <x-stat-card value="PZSS" label="Polski Związek Strzelectwa Sportowego" />
        <x-stat-card value="24/7" label="kontakt e-mail" />
    </section>

    <section class="page-container page-section" aria-labelledby="address-title">
        <x-section-heading id="address-title" title="Dane teleadresowe" meta="HQ_DATA" />

        <div class="bento-grid ui-grid ui-grid--3">
            <x-content-card
                code="SIEDZIBA KLUBU"
                title="{{ $club['name'] }}"
                status="LOKALIZACJA GŁÓWNA"
                href="#lokalizacje"
                link-label="Mapa dojazdu →"
            >
                <p><strong>Adres:</strong> {{ $club['address']['formatted'] }}</p>
                <p>
                    <strong>E-mail:</strong>
                    <a href="mailto:{{ $club['email'] }}" class="card-link">
                        {{ $club['email'] }}
                    </a>
                </p>
            </x-content-card>

            <x-content-card
                class="bento-span-2"
                code="DANE DO FAKTUR VAT"
                title="Dane księgowe"
                status="OFICJALNE ROZLICZENIA"
                link-label="NIP {{ $club['invoice']['nip'] }}"
            >
                <p class="accounting-desc-main">{{ $club['invoice']['name'] }}</p>
                <p class="accounting-desc-sub">Skrót: {{ $club['invoice']['short_name'] }}</p>
                <p><strong>Adres:</strong> {{ $club['invoice']['address'] }}</p>
                <p><strong>NIP:</strong> {{ $club['invoice']['nip'] }}</p>
            </x-content-card>
        </div>
    </section>

    <section class="page-container page-section" aria-labelledby="positions-title">
        <x-section-heading id="positions-title" title="Władze i funkcje klubowe" meta="CLUB_DIRECTORY" />

        @if ($positions->isEmpty())
            <p class="empty-state">
                Lista osób funkcyjnych nie została jeszcze uzupełniona w panelu administracyjnym.
            </p>
        @else
            <div class="bento-grid ui-grid ui-grid--3">
                @foreach ($positions as $position)
                    <x-content-card
                        code="{{ mb_strtoupper($position->name) }}"
                        title="{{ $position->name }}"
                        status="{{ $position->users->count() }} OSÓB"
                        link-label="FUNKCJA KLUBOWA"
                    >
                        @if ($position->description)
                            <p>{{ $position->description }}</p>
                        @endif

                        @if ($position->users->isNotEmpty())
                            <div class="vices-grid">
                                @foreach ($position->users as $person)
                                    <div class="vice-item">
                                        <strong class="vice-name">{{ $person->name }}</strong>

                                        @if ($person->show_phone_publicly && $person->phone)
                                            <a href="tel:{{ $person->phone }}" class="vice-phone">
                                                {{ $person->phone }}
                                            </a>
                                        @endif

                                        @if ($person->show_email_publicly && ! str_ends_with($person->email, '@ks-krokus.local'))
                                            <a href="mailto:{{ $person->email }}" class="card-link vice-mail">
                                                {{ $person->email }}
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p>Brak przypisanej osoby.</p>
                        @endif
                    </x-content-card>
                @endforeach
            </div>
        @endif
    </section>

    <section class="page-container page-section" aria-labelledby="trainers-title">
        <x-section-heading id="trainers-title" title="Trenerzy i instruktorzy" meta="TRAINING_STAFF" />

        @if ($trainers->isEmpty())
            <p class="empty-state">
                Lista trenerów nie została jeszcze uzupełniona w panelu.
            </p>
        @else
            <div class="trainers-grid ui-grid ui-grid--2">
                @foreach ($trainers as $trainer)
                    <div class="trainer-card">
                        <strong class="trainer-name">{{ $trainer->name }}</strong>

                        @if ($trainer->show_phone_publicly && $trainer->phone)
                            <a href="tel:{{ $trainer->phone }}" class="trainer-phone">
                                {{ $trainer->phone }}
                            </a>
                        @endif

                        @if ($trainer->show_email_publicly && ! str_ends_with($trainer->email, '@ks-krokus.local'))
                            <a href="mailto:{{ $trainer->email }}" class="card-link">
                                {{ $trainer->email }}
                            </a>
                        @endif

                        @if ($trainer->trainer_bio)
                            <p class="trainers-desc">{{ $trainer->trainer_bio }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section id="lokalizacje" class="page-container page-section" aria-labelledby="locations-title">
        <x-section-heading id="locations-title" title="Lokalizacje i mapy dojazdu" meta="GEO_LOCATIONS" />

        <div class="bento-grid ui-grid ui-grid--3">
            @foreach ($club['locations'] as $location)
                <x-content-card
                    class="{{ $location['span'] === 2 ? 'bento-span-2' : '' }}"
                    code="{{ $location['type'] }}"
                    title="{{ $location['name'] }}"
                >
                    <p class="location-desc">{{ $location['description'] }}</p>

                    <div class="map-wrapper">
                        <iframe
                            title="Mapa dojazdu: {{ $location['name'] }}"
                            src="{{ $location['map_embed_url'] }}"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen
                        ></iframe>
                    </div>

                    <x-slot:footer>
                        <span class="card-status">{{ $location['status'] }}</span>
                        <a
                            href="{{ $location['map_url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="card-link"
                            aria-label="Otwórz mapę: {{ $location['name'] }} — otwiera w nowej karcie"
                        >
                            Otwórz mapę ↗
                        </a>
                    </x-slot:footer>
                </x-content-card>
            @endforeach
        </div>
    </section>

    <section id="formularz-kontaktowy" class="page-container page-section" aria-labelledby="contact-form-title">
        <x-section-heading id="contact-form-title" title="Napisz do zarządu" meta="CONTACT_FORM" />

        <form method="POST" action="{{ route('contact.send') }}" class="contact-form form-layout panel-card">
            @csrf
            <x-form-errors />

            <div class="contact-form__grid form-grid">
                <label for="contact-name">
                    <span class="form-label-text">
                        Imię i nazwisko <span class="form-required" aria-hidden="true">*</span>
                        <span class="sr-only">(pole wymagane)</span>
                    </span>
                    <input id="contact-name" type="text" name="name" value="{{ old('name') }}" autocomplete="name"
                        minlength="2" maxlength="120" required
                        @error('name') aria-invalid="true" aria-describedby="contact-name-error" @enderror>
                    @error('name') <span id="contact-name-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                </label>

                <label for="contact-email">
                    <span class="form-label-text">
                        Adres e-mail <span class="form-required" aria-hidden="true">*</span>
                        <span class="sr-only">(pole wymagane)</span>
                    </span>
                    <input id="contact-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email"
                        maxlength="255" required
                        @error('email') aria-invalid="true" aria-describedby="contact-email-error" @enderror>
                    @error('email') <span id="contact-email-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                </label>

                <label for="contact-phone">
                    <span class="form-label-text">
                        Telefon <span class="form-optional">(opcjonalnie)</span>
                    </span>
                    <input id="contact-phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel"
                        maxlength="32"
                        @error('phone') aria-invalid="true" aria-describedby="contact-phone-error" @enderror>
                    @error('phone') <span id="contact-phone-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                </label>

                <label for="contact-subject">
                    <span class="form-label-text">
                        Temat <span class="form-required" aria-hidden="true">*</span>
                        <span class="sr-only">(pole wymagane)</span>
                    </span>
                    <input id="contact-subject" type="text" name="subject" value="{{ old('subject') }}"
                        minlength="3" maxlength="150" autocomplete="off" required
                        @error('subject') aria-invalid="true" aria-describedby="contact-subject-error" @enderror>
                    @error('subject') <span id="contact-subject-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                </label>

                <label class="contact-form__message form-grid--span-full" for="contact-message">
                    <span class="form-label-text">
                        Wiadomość <span class="form-required" aria-hidden="true">*</span>
                        <span class="sr-only">(pole wymagane)</span>
                    </span>
                    <textarea id="contact-message" name="message" rows="8" minlength="10" maxlength="5000" required
                        aria-describedby="contact-message-help @error('message') contact-message-error @enderror"
                        @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea>
                    <span id="contact-message-help" class="form-help">Od 10 do 5000 znaków.</span>
                    @error('message') <span id="contact-message-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                </label>
            </div>

            <div class="honeypot" aria-hidden="true">
                <label for="contact-website">Strona internetowa</label>
                <input id="contact-website" type="text" name="website" value="" tabindex="-1" autocomplete="off">
            </div>

            <p class="contact-form__privacy">
                Wysyłając formularz, przekazujesz dane w celu obsługi wiadomości.
                Szczegóły znajdziesz w <a href="{{ route('rodo') }}">klauzuli RODO</a>.
            </p>

            <div class="form-actions">
                <div class="form-actions__primary">
                    <button type="submit" class="btn btn-primary">Wyślij wiadomość</button>
                </div>
            </div>
        </form>
    </section>
@endsection
