@extends('layouts.app')

@section('title', $listing->title.' — ogłoszenia KS Krokus')
@section('body_class', 'listing-detail-page')

@php
    $primaryImage = $listing->images->first();
    $hasPublicContact = ($listing->show_phone && $listing->contact_phone)
        || ($listing->show_email && $listing->contact_email);
@endphp

@section('content')
    <section class="page-container page-section listing-detail" aria-labelledby="listing-title">
        <a href="{{ route('listings.index') }}" class="content-back">← Wróć do ogłoszeń</a>
        <x-form-errors />

        <header class="listing-detail__header">
            <div>
                <span class="listing-category-badge">{{ $listing->category->label() }}</span>
                <h1 id="listing-title">{{ $listing->title }}</h1>
                <p>Opublikowano <time datetime="{{ $listing->published_at?->toDateString() }}">{{ $listing->published_at?->format('d.m.Y') }}</time></p>
            </div>
        </header>

        <div class="listing-detail__layout sidebar-layout">
            <div class="listing-detail__main ui-stack">
                <section class="listing-gallery" aria-labelledby="listing-gallery-title" data-listing-gallery>
                    <h2 id="listing-gallery-title" class="sr-only">Galeria zdjęć ogłoszenia</h2>

                    <figure class="listing-gallery__stage">
                        @if ($primaryImage)
                            <a href="{{ $primaryImage->url() }}" data-listing-gallery-open aria-label="Powiększ zdjęcie: {{ $primaryImage->alt_text ?: $listing->title }}">
                                <img
                                    src="{{ $primaryImage->url() }}"
                                    alt="{{ $primaryImage->alt_text ?: $listing->title }}"
                                    data-listing-gallery-main
                                >
                            </a>
                            <figcaption data-listing-gallery-caption @if (! $primaryImage->caption) hidden @endif>{{ $primaryImage->caption }}</figcaption>
                        @else
                            <x-image-placeholder label="Brak zdjęć ogłoszenia" />
                        @endif
                    </figure>

                    @if ($listing->images->count() > 1)
                        <div class="listing-gallery__thumbnails" aria-label="Wybierz zdjęcie">
                            @foreach ($listing->images as $image)
                                <a
                                    href="{{ $image->url() }}"
                                    class="listing-gallery__thumbnail"
                                    data-listing-gallery-thumbnail
                                    data-src="{{ $image->url() }}"
                                    data-alt="{{ $image->alt_text ?: $listing->title }}"
                                    data-caption="{{ $image->caption }}"
                                    aria-label="Pokaż zdjęcie {{ $loop->iteration }} z {{ $listing->images->count() }}"
                                    @if ($loop->first) aria-current="true" @endif
                                >
                                    <img src="{{ $image->thumbnailUrl() }}" alt="" loading="lazy">
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="listing-detail__panel panel-card" aria-labelledby="listing-description-heading">
                    <h2 id="listing-description-heading">Opis</h2>
                    <div class="listing-description">{!! nl2br(e($listing->description)) !!}</div>
                </section>

                <section class="listing-detail__panel panel-card" aria-labelledby="listing-parameters-heading">
                    <h2 id="listing-parameters-heading">Parametry</h2>
                    <dl class="listing-facts">
                        <div><dt>Kategoria</dt><dd>{{ $listing->category->label() }}</dd></div>
                        @if ($listing->firearm_type)<div><dt>Rodzaj</dt><dd>{{ $listing->firearm_type->label() }}</dd></div>@endif
                        @if ($listing->manufacturer)<div><dt>Producent</dt><dd>{{ $listing->manufacturer }}</dd></div>@endif
                        @if ($listing->model)<div><dt>Model</dt><dd>{{ $listing->model }}</dd></div>@endif
                        @if ($listing->caliber)<div><dt>Kaliber</dt><dd>{{ $listing->caliber }}</dd></div>@endif
                        @if ($listing->condition)<div><dt>Stan</dt><dd>{{ $listing->condition->label() }}</dd></div>@endif
                        @if ($listing->year_of_manufacture)<div><dt>Rok produkcji</dt><dd>{{ $listing->year_of_manufacture }}</dd></div>@endif
                        @if ($listing->location)<div><dt>Lokalizacja</dt><dd>{{ $listing->location }}</dd></div>@endif
                        @if ($listing->expires_at)<div><dt>Wygasa</dt><dd><time datetime="{{ $listing->expires_at->toDateString() }}">{{ $listing->expires_at->format('d.m.Y') }}</time></dd></div>@endif
                    </dl>
                </section>
            </div>

            <aside class="listing-contact-card panel-card" aria-labelledby="listing-contact-heading">
                <div class="listing-detail__price">
                    <span>Cena</span>
                    <strong>{{ $listing->formattedPrice() }}</strong>
                    @if ($listing->price_negotiable)<span class="listing-negotiable">Do negocjacji</span>@endif
                </div>

                <div class="listing-contact-card__content">
                    <h2 id="listing-contact-heading">Kontakt ze sprzedającym</h2>
                    <p class="listing-contact-card__name">{{ $listing->contact_name ?: $listing->author->name }}</p>

                    @if ($hasPublicContact)
                        <div class="listing-contact-actions">
                            @if ($listing->show_phone && $listing->contact_phone)
                                <a href="tel:{{ preg_replace('/\s+/', '', $listing->contact_phone) }}" class="btn btn-primary">
                                    Zadzwoń <span class="listing-contact-actions__value">{{ $listing->contact_phone }}</span>
                                </a>
                            @endif
                            @if ($listing->show_email && $listing->contact_email)
                                <a href="mailto:{{ $listing->contact_email }}" class="btn btn-secondary">
                                    Napisz wiadomość <span class="listing-contact-actions__value">{{ $listing->contact_email }}</span>
                                </a>
                            @endif
                        </div>
                    @else
                        <p class="listing-contact-card__empty">Autor nie udostępnił publicznie telefonu ani adresu e-mail.</p>
                    @endif
                </div>

                <div class="listing-disclaimer">
                    <strong>Bezpieczna transakcja</strong>
                    <p>KS Krokus nie pośredniczy w transakcji, nie pobiera płatności i nie odpowiada za jej przebieg. Strony samodzielnie weryfikują wymagane uprawnienia oraz zgodność transakcji z prawem.</p>
                </div>
            </aside>
        </div>

        <section class="listing-report-section panel-card" aria-labelledby="listing-report-heading">
            <details class="listing-report" @if ($errors->hasAny(['reason', 'details', 'website'])) open @endif>
                <summary id="listing-report-heading">Zgłoś nieaktualne lub niewłaściwe ogłoszenie</summary>
                <p>Zgłoszenie trafi do administratora i nie jest widoczne dla sprzedającego.</p>
                <form method="POST" action="{{ route('listings.report', $listing) }}" class="contact-form">
                    @csrf
                    <label for="listing-report-reason">
                        <span class="form-label-text">
                            Powód zgłoszenia <span class="form-required" aria-hidden="true">*</span>
                            <span class="sr-only">(pole wymagane)</span>
                        </span>
                        <select id="listing-report-reason" name="reason" required @error('reason') aria-invalid="true" aria-describedby="listing-report-reason-error" @enderror>
                            <option value="">Wybierz powód</option>
                            @foreach ($reportReasons as $value => $label)
                                <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('reason') <span id="listing-report-reason-error" class="form-error">{{ $message }}</span> @enderror
                    </label>
                    <label for="listing-report-details">
                        Dodatkowe informacje
                        <textarea id="listing-report-details" name="details" rows="4" maxlength="2000" @error('details') aria-invalid="true" aria-describedby="listing-report-details-error" @enderror>{{ old('details') }}</textarea>
                        @error('details') <span id="listing-report-details-error" class="form-error">{{ $message }}</span> @enderror
                    </label>
                    <div class="form-honeypot" aria-hidden="true"><label>Strona internetowa <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                    <button type="submit" class="btn btn-secondary">Wyślij zgłoszenie</button>
                </form>
            </details>
        </section>

        @if ($primaryImage)
            <dialog class="listing-lightbox" aria-labelledby="listing-lightbox-title" data-listing-lightbox>
                <div class="listing-lightbox__header">
                    <h2 id="listing-lightbox-title">Powiększone zdjęcie ogłoszenia</h2>
                    <button type="button" class="listing-lightbox__close" aria-label="Zamknij podgląd zdjęcia" data-listing-lightbox-close>×</button>
                </div>
                <figure>
                    <img src="{{ $primaryImage->url() }}" alt="{{ $primaryImage->alt_text ?: $listing->title }}" data-listing-lightbox-image>
                    <figcaption data-listing-lightbox-caption @if (! $primaryImage->caption) hidden @endif>{{ $primaryImage->caption }}</figcaption>
                </figure>
            </dialog>
        @endif
    </section>
@endsection
