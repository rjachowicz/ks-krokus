@extends('layouts.app')

@section('title', $listing->title.' — ogłoszenia KS Krokus')
@section('body_class', 'listing-detail-page')

@section('content')
    <section class="content-section listing-detail" aria-labelledby="listing-title">
        <div class="content-container">
            <a href="{{ route('listings.index') }}" class="content-back">← Wróć do ogłoszeń</a>

            <header class="listing-detail__header">
                <div>
                    <span class="listing-status">Aktualne</span>
                    <p class="listing-detail__category">{{ $listing->category->label() }}</p>
                    <h1 id="listing-title">{{ $listing->title }}</h1>
                    <p>Opublikowano <time datetime="{{ $listing->published_at?->toDateString() }}">{{ $listing->published_at?->format('d.m.Y') }}</time></p>
                </div>
                <div class="listing-detail__price">
                    <strong>{{ $listing->formattedPrice() }}</strong>
                    @if ($listing->price_negotiable)<span>Cena do negocjacji</span>@endif
                </div>
            </header>

            <div class="listing-gallery" aria-label="Galeria zdjęć ogłoszenia">
                @foreach ($listing->images as $image)
                    <figure class="{{ $image->is_primary ? 'listing-gallery__primary' : '' }}">
                        <a href="{{ $image->url() }}" target="_blank" rel="noopener noreferrer" aria-label="Otwórz zdjęcie w pełnym rozmiarze — nowa karta">
                            <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?: $listing->title }}">
                        </a>
                        @if ($image->caption)<figcaption>{{ $image->caption }}</figcaption>@endif
                    </figure>
                @endforeach
            </div>

            <div class="listing-detail__grid">
                <article class="listing-detail__content">
                    <h2>Opis</h2>
                    <div class="listing-description">{!! nl2br(e($listing->description)) !!}</div>

                    <h2>Parametry</h2>
                    <dl class="listing-facts">
                        <div><dt>Kategoria</dt><dd>{{ $listing->category->label() }}</dd></div>
                        @if ($listing->firearm_type)<div><dt>Rodzaj</dt><dd>{{ $listing->firearm_type->label() }}</dd></div>@endif
                        @if ($listing->manufacturer)<div><dt>Producent</dt><dd>{{ $listing->manufacturer }}</dd></div>@endif
                        @if ($listing->model)<div><dt>Model</dt><dd>{{ $listing->model }}</dd></div>@endif
                        @if ($listing->caliber)<div><dt>Kaliber</dt><dd>{{ $listing->caliber }}</dd></div>@endif
                        @if ($listing->condition)<div><dt>Stan</dt><dd>{{ $listing->condition->label() }}</dd></div>@endif
                        @if ($listing->year_of_manufacture)<div><dt>Rok produkcji</dt><dd>{{ $listing->year_of_manufacture }}</dd></div>@endif
                        @if ($listing->location)<div><dt>Lokalizacja</dt><dd>{{ $listing->location }}</dd></div>@endif
                        <div><dt>Wygasa</dt><dd><time datetime="{{ $listing->expires_at?->toDateString() }}">{{ $listing->expires_at?->format('d.m.Y') }}</time></dd></div>
                    </dl>
                </article>

                <aside class="listing-contact-card">
                    <h2>Kontakt ze sprzedającym</h2>
                    <p><strong>{{ $listing->contact_name ?: $listing->author->name }}</strong></p>
                    @if ($listing->show_phone && $listing->contact_phone)
                        <p><a href="tel:{{ preg_replace('/\s+/', '', $listing->contact_phone) }}">{{ $listing->contact_phone }}</a></p>
                    @endif
                    @if ($listing->show_email && $listing->contact_email)
                        <p><a href="mailto:{{ $listing->contact_email }}">{{ $listing->contact_email }}</a></p>
                    @endif
                    @unless (($listing->show_phone && $listing->contact_phone) || ($listing->show_email && $listing->contact_email))
                        <p>Autor nie udostępnił publicznie telefonu ani adresu e-mail.</p>
                    @endunless

                    <div class="listing-disclaimer">
                        <strong>Ważna informacja</strong>
                        <p>KS Krokus nie pośredniczy w transakcji, nie pobiera płatności i nie odpowiada za jej przebieg. Strony samodzielnie weryfikują wymagane uprawnienia oraz zgodność transakcji z prawem.</p>
                    </div>

                    <details class="listing-report">
                        <summary>Zgłoś nieaktualne lub niewłaściwe ogłoszenie</summary>
                        <form method="POST" action="{{ route('listings.report', $listing) }}" class="contact-form">
                            @csrf
                            <label>
                                Powód zgłoszenia
                                <select name="reason" required @error('reason') aria-invalid="true" aria-describedby="listing-report-reason-error" @enderror>
                                    <option value="">Wybierz powód</option>
                                    @foreach ($reportReasons as $value => $label)<option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>@endforeach
                                </select>
                                @error('reason') <span id="listing-report-reason-error" class="form-error">{{ $message }}</span> @enderror
                            </label>
                            <label>
                                Dodatkowe informacje
                                <textarea name="details" rows="4" maxlength="2000">{{ old('details') }}</textarea>
                            </label>
                            <div class="form-honeypot" aria-hidden="true"><label>Strona internetowa <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                            <button type="submit" class="btn btn-secondary">Wyślij zgłoszenie</button>
                        </form>
                    </details>
                </aside>
            </div>
        </div>
    </section>
@endsection
