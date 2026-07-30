@extends('layouts.app')

@section('title', 'Kontakt — KS Krokus Nowy Sącz')
@section('meta_description', 'Dane kontaktowe, zarząd, komisja rewizyjna, instruktorzy i lokalizacje Klubu Strzeleckiego Krokus LOK w Nowym Sączu.')

@php
    $club = config('club');
    $contacts = config('contacts');
@endphp

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
        <x-slot:title>Skontaktuj się z <span class="highlight">Krokusem</span></x-slot:title>
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
                    <a href="mailto:{{ $club['email'] }}" class="card-link inline-mail-link">{{ $club['email'] }}</a>
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

    <section class="features-section" aria-labelledby="board-title">
        <x-section-heading id="board-title" title="Zarząd klubu" meta="BOARD_DIRECTORS" />

        <div class="bento-grid">
            @foreach ($contacts['board'] as $member)
                <x-content-card
                    code="{{ $member['role'] }}"
                    title="{{ $member['name'] }}"
                    status="KONTAKT"
                    :href="'mailto:'.$member['email']"
                    link-label="{{ $member['email'] }}"
                >
                    @if ($member['phone'])
                        <p class="board-desc-phone">
                            <a href="tel:{{ $member['phone'] }}" class="club-link">{{ $member['phone_display'] }}</a>
                        </p>
                    @endif
                    <p class="board-desc">{{ $member['description'] }}</p>
                </x-content-card>
            @endforeach

            <x-content-card class="bento-span-2" code="WICEPREZESI" title="Władze wykonawcze">
                <div class="vices-grid">
                    @foreach ($contacts['vice_presidents'] as $vice)
                        <div class="vice-item">
                            <strong class="vice-name">{{ $vice['name'] }}</strong>
                            <a href="tel:{{ $vice['phone'] }}" class="vice-phone">{{ $vice['phone_display'] }}</a>
                            <a href="mailto:{{ $vice['email'] }}" class="card-link vice-mail">{{ $vice['email'] }}</a>
                        </div>
                    @endforeach
                </div>
            </x-content-card>
        </div>
    </section>

    <section class="features-section" aria-labelledby="audit-title">
        <x-section-heading id="audit-title" title="Komisja rewizyjna i szkolenia" meta="AUDIT_AND_TRAINING" />

        <div class="bento-grid">
            <x-content-card
                code="NADZÓR KONTROLNY"
                title="Komisja Rewizyjna"
                status="ORGAN KONTROLNY"
                link-label="AUDIT"
            >
                <p class="audit-mail-wrapper">
                    <a href="mailto:{{ $contacts['audit_committee']['email'] }}" class="card-link">
                        {{ $contacts['audit_committee']['email'] }}
                    </a>
                </p>

                <ul class="audit-list">
                    @foreach ($contacts['audit_committee']['members'] as $member)
                        <li><strong>{{ $member['role'] }}:</strong> {{ $member['name'] }}</li>
                    @endforeach
                </ul>
            </x-content-card>

            <x-content-card
                class="bento-span-2"
                code="PATENT STRZELECKI"
                title="Osoby przygotowujące do egzaminu"
                status="SZKOLENIA PZSS"
                :href="route('club').'#szkolenie-patentowe'"
                link-label="Informacje o szkoleniu →"
            >
                <p class="trainers-desc">Kontakt do instruktorów prowadzących szkolenia przygotowawcze:</p>

                <div class="trainers-grid">
                    @foreach ($contacts['trainers'] as $trainer)
                        <div class="trainer-card">
                            <strong class="trainer-name">{{ $trainer['name'] }}</strong>
                            <a href="tel:{{ $trainer['phone'] }}" class="trainer-phone">{{ $trainer['phone_display'] }}</a>
                        </div>
                    @endforeach
                </div>
            </x-content-card>
        </div>
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
                        <a href="{{ $location['map_url'] }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="card-link">
                            Otwórz mapę →
                        </a>
                    </x-slot:footer>
                </x-content-card>
            @endforeach
        </div>
    </section>
@endsection
