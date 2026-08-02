@extends('layouts.app')

@section('title', 'Ogłoszenia sprzedaży — KS Krokus')
@section('body_class', 'listings-page')

@php
    $filterKeys = ['q', 'category', 'type', 'caliber', 'price_from', 'price_to'];
    $advancedFilterKeys = ['type', 'caliber', 'price_from', 'price_to'];
    $hasFilters = request()->anyFilled($filterKeys);
    $activeAdvancedFilters = collect($advancedFilterKeys)->filter(fn (string $key): bool => request()->filled($key))->count();
    $listingCount = $listings->total();
    $listingCountLabel = $listingCount === 1
        ? 'ogłoszenie'
        : (($listingCount % 10 >= 2 && $listingCount % 10 <= 4 && ($listingCount % 100 < 12 || $listingCount % 100 > 14))
            ? 'ogłoszenia'
            : 'ogłoszeń');
@endphp

@section('content')
    <x-page-hero id="listings-page-title" eyebrow="TABLICA KLUBOWA" class="content-hero">
        <x-slot:title>Ogłoszenia <span class="highlight">klubowe</span></x-slot:title>
        <x-slot:description>
            <p>Przeglądaj przedmioty związane ze sportem strzeleckim oferowane przez członków KS Krokus. Klub udostępnia tablicę informacyjnie i nie pośredniczy w transakcjach.</p>
        </x-slot:description>
        @auth
            <x-slot:actions>
                <a href="{{ route('admin.my-listings.create') }}" class="btn btn-primary">Dodaj ogłoszenie</a>
            </x-slot:actions>
        @endauth
    </x-page-hero>

    <section class="features-section features-section--flush" aria-labelledby="listings-heading">
        <x-form-errors />

        <form method="GET" action="{{ route('listings.index') }}" class="listing-filters" aria-label="Filtrowanie ogłoszeń">
            @if (request()->filled('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif

            <div class="listing-filters__primary">
                <label class="listing-filters__search" for="listing-filter-q">
                    Szukaj
                    <input id="listing-filter-q" type="search" name="q" maxlength="100" value="{{ request('q') }}" placeholder="Tytuł, producent lub model" autocomplete="off"
                        @error('q') aria-invalid="true" aria-describedby="listing-filter-q-error" @enderror>
                    @error('q') <span id="listing-filter-q-error" class="form-error">{{ $message }}</span> @enderror
                </label>

                <label for="listing-filter-category">
                    Kategoria
                    <select id="listing-filter-category" name="category" @error('category') aria-invalid="true" aria-describedby="listing-filter-category-error" @enderror>
                        <option value="">Wszystkie kategorie</option>
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category') <span id="listing-filter-category-error" class="form-error">{{ $message }}</span> @enderror
                </label>

                <button class="btn btn-primary" type="submit">Szukaj</button>
            </div>

            <details class="listing-filters__more" @if ($activeAdvancedFilters > 0 || $errors->hasAny($advancedFilterKeys)) open @endif>
                <summary>
                    <span>Więcej filtrów</span>
                    @if ($activeAdvancedFilters > 0)
                        <span class="listing-filters__count">Aktywne: {{ $activeAdvancedFilters }}</span>
                    @endif
                </summary>

                <div class="listing-filters__advanced">
                    <label for="listing-filter-type">
                        Rodzaj
                        <select id="listing-filter-type" name="type" @error('type') aria-invalid="true" aria-describedby="listing-filter-type-error" @enderror>
                            <option value="">Wszystkie rodzaje</option>
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type') <span id="listing-filter-type-error" class="form-error">{{ $message }}</span> @enderror
                    </label>

                    @if ($calibers->isNotEmpty())
                        <label for="listing-filter-caliber">
                            Kaliber
                            <select id="listing-filter-caliber" name="caliber" @error('caliber') aria-invalid="true" aria-describedby="listing-filter-caliber-error" @enderror>
                                <option value="">Wszystkie kalibry</option>
                                @foreach ($calibers as $caliber)
                                    <option value="{{ $caliber }}" @selected(request('caliber') === $caliber)>{{ $caliber }}</option>
                                @endforeach
                            </select>
                            @error('caliber') <span id="listing-filter-caliber-error" class="form-error">{{ $message }}</span> @enderror
                        </label>
                    @endif

                    <label for="listing-filter-price-from">
                        Cena od
                        <input id="listing-filter-price-from" type="number" name="price_from" min="0" step="0.01" value="{{ request('price_from') }}" inputmode="decimal"
                            @error('price_from') aria-invalid="true" aria-describedby="listing-filter-price-from-error" @enderror>
                        @error('price_from') <span id="listing-filter-price-from-error" class="form-error">{{ $message }}</span> @enderror
                    </label>

                    <label for="listing-filter-price-to">
                        Cena do
                        <input id="listing-filter-price-to" type="number" name="price_to" min="0" step="0.01" value="{{ request('price_to') }}" inputmode="decimal"
                            @error('price_to') aria-invalid="true" aria-describedby="listing-filter-price-to-error" @enderror>
                        @error('price_to') <span id="listing-filter-price-to-error" class="form-error">{{ $message }}</span> @enderror
                    </label>

                    <div class="listing-filters__advanced-actions">
                        <button class="btn btn-secondary" type="submit">Zastosuj filtry</button>
                        @if ($hasFilters)
                            <a href="{{ route('listings.index') }}" class="listing-filters__clear">Wyczyść wszystkie filtry</a>
                        @endif
                    </div>
                </div>
            </details>
        </form>

        <div class="listing-results-bar">
            <div>
                <h2 id="listings-heading">Aktualne ogłoszenia</h2>
                <p>
                    Znaleziono: <strong>{{ $listingCount }}</strong> {{ $listingCountLabel }}
                </p>
            </div>

            <form method="GET" action="{{ route('listings.index') }}" class="listing-sort" aria-label="Sortowanie ogłoszeń">
                @foreach ($filterKeys as $filterKey)
                    @if (request()->filled($filterKey))
                        <input type="hidden" name="{{ $filterKey }}" value="{{ request($filterKey) }}">
                    @endif
                @endforeach
                <label for="listing-sort">
                    Sortuj
                    <select id="listing-sort" name="sort" @error('sort') aria-invalid="true" aria-describedby="listing-sort-error" @enderror>
                        <option value="newest" @selected(request('sort', 'newest') === 'newest')>Najnowsze</option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>Najstarsze</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Cena rosnąco</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Cena malejąco</option>
                    </select>
                    @error('sort') <span id="listing-sort-error" class="form-error">{{ $message }}</span> @enderror
                </label>
                <button class="btn btn-secondary" type="submit">Sortuj</button>
            </form>
        </div>

        @if ($listings->isEmpty())
            <div class="content-empty listing-empty">
                <h3>Brak ogłoszeń</h3>
                <p>{{ $hasFilters ? 'Nie znaleziono aktualnych ofert spełniających wybrane kryteria.' : 'Nie opublikowano jeszcze żadnych ofert.' }}</p>
                @if ($hasFilters)
                    <a href="{{ route('listings.index') }}" class="btn btn-secondary">Wyczyść filtry</a>
                @endif
            </div>
        @else
            <div class="listing-grid">
                @foreach ($listings as $listing)
                    @php
                        $keyParameters = collect([
                            $listing->firearm_type?->label(),
                            $listing->caliber,
                            $listing->condition?->label(),
                        ])->filter()->take(2);
                    @endphp
                    <article class="listing-card">
                        <a href="{{ route('listings.show', $listing) }}" class="listing-card__image" aria-label="Zobacz ogłoszenie: {{ $listing->title }}">
                            @if ($listing->primaryImage)
                                <img src="{{ $listing->primaryImage->thumbnailUrl() }}" alt="{{ $listing->primaryImage->alt_text ?: $listing->title }}" loading="lazy">
                            @else
                                <x-image-placeholder />
                            @endif
                        </a>

                        <div class="listing-card__body">
                            <span class="listing-category-badge">{{ $listing->category->label() }}</span>
                            <h3><a href="{{ route('listings.show', $listing) }}">{{ $listing->title }}</a></h3>

                            @if ($listing->manufacturer || $listing->model)
                                <p class="listing-card__model">{{ trim($listing->manufacturer.' '.$listing->model) }}</p>
                            @endif

                            @if ($keyParameters->isNotEmpty())
                                <ul class="listing-card__parameters" aria-label="Najważniejsze parametry">
                                    @foreach ($keyParameters as $parameter)
                                        <li>{{ $parameter }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <p class="listing-card__description">{{ str($listing->description)->limit(180) }}</p>

                            <footer class="listing-card__footer">
                                <div>
                                    <strong class="listing-price">{{ $listing->formattedPrice() }}</strong>
                                    @if ($listing->price_negotiable)<span>Do negocjacji</span>@endif
                                </div>
                                <div class="listing-card__place-date">
                                    <span>{{ $listing->location ?: 'Lokalizacja do uzgodnienia' }}</span>
                                    <time datetime="{{ $listing->published_at?->toDateString() }}">{{ $listing->published_at?->format('d.m.Y') }}</time>
                                </div>
                            </footer>
                        </div>
                    </article>
                @endforeach
            </div>
            {{ $listings->links() }}
        @endif
    </section>
@endsection
