@extends('layouts.app')

@section('title', 'Kontakt — KS Krokus Nowy Sącz')
@section('meta_description', 'Dane kontaktowe, władze klubu, trenerzy i lokalizacje Klubu Strzeleckiego Krokus LOK w Nowym Sączu.')

@php($club = config('club'))

@section('content')
    <x-page-hero
        id="contact-title"
        eyebrow="Centrala i władze klubu"
        class="contact-hero"
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

    <section class="hero-stats-bar" aria-label="Organizacje i kanały kontaktu">
        <x-stat-card value="LOK" label="Liga Obrony Kraju" />
        <x-stat-card value="PZSS" label="Polski Związek Strzelectwa Sportowego" />
        <x-stat-card value="24/7" label="kontakt e-mail" />
    </section>

    <section class="features-section" aria-labelledby="address-title">
        <x-section-heading id="address-title" title="Dane teleadresowe" meta="HQ_DATA" />

        <div class="bento-grid">
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
                    <a href="mailto:{{ $club['email'] }}" class="card-link inline-mail-link">
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

    <section class="features-section" aria-labelledby="positions-title">
        <x-section-heading id="positions-title" title="Władze i funkcje klubowe" meta="CLUB_DIRECTORY" />

        @if ($positions->isEmpty())
            <div class="content-empty">
                Lista osób funkcyjnych nie została jeszcze uzupełniona w panelu administracyjnym.
            </div>
        @else
            <div class="bento-grid">
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

    <section class="features-section" aria-labelledby="trainers-title">
        <x-section-heading id="trainers-title" title="Trenerzy i instruktorzy" meta="TRAINING_STAFF" />

        @if ($trainers->isEmpty())
            <div class="content-empty">
                Lista trenerów nie została jeszcze uzupełniona w panelu.
            </div>
        @else
            <div class="trainers-grid">
                @foreach ($trainers as $trainer)
                    <article class="trainer-card">
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
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section id="formularz-kontaktowy" class="features-section" aria-labelledby="contact-form-title">
        <x-section-heading id="contact-form-title" title="Napisz do zarządu" meta="CONTACT_FORM" />

        <form method="POST" action="{{ route('contact.send') }}" class="contact-form">
            @csrf
            <x-form-errors />

            <div class="contact-form__grid">
                <label for="contact-name">
                    Imię i nazwisko
                    <input id="contact-name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" required
                        @error('name') aria-invalid="true" aria-describedby="contact-name-error" @enderror>
                    @error('name') <span id="contact-name-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                </label>

                <label for="contact-email">
                    Adres e-mail
                    <input id="contact-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required
                        @error('email') aria-invalid="true" aria-describedby="contact-email-error" @enderror>
                    @error('email') <span id="contact-email-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                </label>

                <label for="contact-phone">
                    Telefon <span class="form-optional">(opcjonalnie)</span>
                    <input id="contact-phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel"
                        @error('phone') aria-invalid="true" aria-describedby="contact-phone-error" @enderror>
                    @error('phone') <span id="contact-phone-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                </label>

                <label for="contact-subject">
                    Temat
                    <input id="contact-subject" type="text" name="subject" value="{{ old('subject') }}" required
                        @error('subject') aria-invalid="true" aria-describedby="contact-subject-error" @enderror>
                    @error('subject') <span id="contact-subject-error" class="form-error" role="alert">{{ $message }}</span> @enderror
                </label>

                <label class="contact-form__message" for="contact-message">
                    Wiadomość
                    <textarea id="contact-message" name="message" rows="8" required
                        @error('message') aria-invalid="true" aria-describedby="contact-message-error" @enderror>{{ old('message') }}</textarea>
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

            <button type="submit" class="btn btn-primary">Wyślij wiadomość</button>
        </form>
    </section>

    <section id="lokalizacje" class="features-section" aria-labelledby="locations-title">
        <x-section-heading id="locations-title" title="Lokalizacje i mapy dojazdu" meta="GEO_LOCATIONS" />

        <div class="bento-grid">
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
                        >
                            Otwórz mapę →
                        </a>
                    </x-slot:footer>
                </x-content-card>
            @endforeach
        </div>
    </section>
@endsection
