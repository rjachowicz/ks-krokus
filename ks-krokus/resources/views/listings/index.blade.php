@extends('layouts.app')

@section('title', 'Ogłoszenia sprzedaży — KS Krokus')
@section('body_class', 'listings-page')

@section('content')
    <x-page-hero id="listings-page-title" eyebrow="TABLICA KLUBOWA" class="content-hero">
        <x-slot:title>Ogłoszenia sprzedaży</x-slot:title>
        <x-slot:description><p>Przedmioty związane ze sportem strzeleckim oferowane przez członków klubu.</p></x-slot:description>
    </x-page-hero>

    <section class="content-section" aria-labelledby="listings-heading">
        <div class="content-container">
            <x-form-errors />

            <form method="GET" action="{{ route('listings.index') }}" class="content-toolbar listing-filters">
                <label>
                    Szukaj
                    <input id="listing-filter-q" type="search" name="q" maxlength="100" value="{{ request('q') }}" placeholder="Tytuł, producent lub model" autocomplete="off" @error('q') aria-invalid="true" aria-describedby="listing-filter-q-error" @enderror>
                    @error('q') <span id="listing-filter-q-error" class="form-error">{{ $message }}</span> @enderror
                </label>
                <label>
                    Kategoria
                    <select name="category">
                        <option value="">Wszystkie</option>
                        @foreach ($categories as $value => $label)<option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>@endforeach
                    </select>
                </label>
                <label>
                    Rodzaj
                    <select name="type">
                        <option value="">Wszystkie</option>
                        @foreach ($types as $value => $label)<option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>@endforeach
                    </select>
                </label>
                @if ($calibers->isNotEmpty())
                    <label>
                        Kaliber
                        <select name="caliber">
                            <option value="">Wszystkie</option>
                            @foreach ($calibers as $caliber)<option value="{{ $caliber }}" @selected(request('caliber') === $caliber)>{{ $caliber }}</option>@endforeach
                        </select>
                    </label>
                @endif
                <label>Cena od <input type="number" name="price_from" min="0" step="0.01" value="{{ request('price_from') }}" inputmode="decimal"></label>
                <label>Cena do <input type="number" name="price_to" min="0" step="0.01" value="{{ request('price_to') }}" inputmode="decimal"></label>
                <label>
                    Sortowanie
                    <select name="sort">
                        <option value="newest" @selected(request('sort', 'newest') === 'newest')>Najnowsze</option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>Najstarsze</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Cena rosnąco</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Cena malejąco</option>
                    </select>
                </label>
                <button class="btn btn-primary" type="submit">Filtruj</button>
                @if (request()->query())<a href="{{ route('listings.index') }}" class="btn btn-secondary">Wyczyść</a>@endif
            </form>

            <div class="section-header">
                <h2 id="listings-heading">Aktualne ogłoszenia</h2>
                @auth<a href="{{ route('admin.my-listings.create') }}" class="btn btn-action">Dodaj ogłoszenie</a>@endauth
            </div>

            @if ($listings->isEmpty())
                <div class="empty-state"><h3>Brak ogłoszeń</h3><p>Nie znaleziono aktualnych ofert spełniających wybrane kryteria.</p></div>
            @else
                <div class="listing-grid">
                    @foreach ($listings as $listing)
                        <article class="listing-card">
                            <a href="{{ route('listings.show', $listing) }}" class="listing-card__image" aria-label="Zobacz ogłoszenie: {{ $listing->title }}">
                                @if ($listing->primaryImage)<img src="{{ $listing->primaryImage->thumbnailUrl() }}" alt="{{ $listing->primaryImage->alt_text ?: $listing->title }}">@endif
                            </a>
                            <div class="listing-card__body">
                                <div class="listing-card__meta"><span class="listing-status">Aktualne</span><span>{{ $listing->category->label() }}</span></div>
                                <h3><a href="{{ route('listings.show', $listing) }}">{{ $listing->title }}</a></h3>
                                @if ($listing->manufacturer || $listing->model)<p class="listing-card__model">{{ trim($listing->manufacturer.' '.$listing->model) }}</p>@endif
                                @if ($listing->caliber)<p>Kaliber: <strong>{{ $listing->caliber }}</strong></p>@endif
                                <p>{{ \Illuminate\Support\Str::limit($listing->description, 165) }}</p>
                                <div class="listing-card__footer">
                                    <strong class="listing-price">{{ $listing->formattedPrice() }}</strong>
                                    @if ($listing->price_negotiable)<span>do negocjacji</span>@endif
                                    <span>{{ $listing->location ?: 'Lokalizacja do uzgodnienia' }}</span>
                                    <time datetime="{{ $listing->published_at?->toDateString() }}">{{ $listing->published_at?->format('d.m.Y') }}</time>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                {{ $listings->links() }}
            @endif
        </div>
    </section>
@endsection
