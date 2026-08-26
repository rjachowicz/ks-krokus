@extends('layouts.admin')

@section('title', 'Moje ogłoszenia — panel KS Krokus')
@section('admin_title', 'Moje ogłoszenia')

@section('content')
    <x-admin-page-header title="Moje ogłoszenia" description="Szkice, moderacja i opublikowane oferty w jednym miejscu.">
        <x-slot:actions><a href="{{ route('admin.my-listings.create') }}" class="btn btn-primary">Dodaj ogłoszenie</a></x-slot:actions>
    </x-admin-page-header>

    <form method="GET" class="admin-filter filter-form panel-card listing-owner-filter" aria-label="Filtrowanie moich ogłoszeń">
        <div class="filter-form__row">
            <label for="my-listings-status">Status
                <select id="my-listings-status" name="status" @error('status') aria-invalid="true" aria-describedby="my-listings-status-error" @enderror>
                    <option value="">Wszystkie statusy</option>
                    @foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach
                </select>
                @error('status') <span id="my-listings-status-error" class="form-error">{{ $message }}</span> @enderror
            </label>
            <div class="filter-form__actions">
                <div class="filter-form__action-group">
                    <button type="submit" class="btn btn-primary">Filtruj</button>
                    @if (request('status'))<a href="{{ route('admin.my-listings.index') }}" class="btn btn-secondary">Wyczyść</a>@endif
                </div>
            </div>
        </div>
    </form>

    @if ($listings->isEmpty())
        <div class="empty-state listing-panel-empty">
            <h2>Brak ogłoszeń</h2>
            <p>{{ request('status') ? 'Nie masz ogłoszeń o wybranym statusie.' : 'Dodaj pierwsze ogłoszenie i zapisz je jako szkic albo wyślij do moderacji.' }}</p>
            @if (request('status'))
                <a href="{{ route('admin.my-listings.index') }}" class="btn btn-secondary">Pokaż wszystkie</a>
            @else
                <a href="{{ route('admin.my-listings.create') }}" class="btn btn-primary">Dodaj ogłoszenie</a>
            @endif
        </div>
    @else
        <div class="owner-listing-list ui-stack">
            @foreach ($listings as $listing)
                @php($thumbnailUrl = $listing->primaryImage?->thumbnailUrl())
                <article class="owner-listing-card panel-card">
                    <div class="owner-listing-card__image">
                        @if ($thumbnailUrl)
                            <img src="{{ $thumbnailUrl }}" alt="{{ $listing->primaryImage->alt_text ?: $listing->title }}" loading="lazy">
                        @else
                            <x-image-placeholder />
                        @endif
                    </div>

                    <div class="owner-listing-card__content">
                        <div class="owner-listing-card__heading">
                            <div>
                                <span class="listing-category-badge">{{ $listing->category->label() }}</span>
                                <h2>{{ $listing->title }}</h2>
                                <p>{{ $listing->formattedPrice() }}</p>
                            </div>
                            <span class="status-badge {{ $listing->status->badgeClass() }}">{{ $listing->status->label() }}</span>
                        </div>

                        <dl class="owner-listing-card__dates">
                            <div><dt>Aktualizacja</dt><dd><time datetime="{{ $listing->updated_at->toDateString() }}">{{ $listing->updated_at->format('d.m.Y H:i') }}</time></dd></div>
                            <div><dt>Wygaśnięcie</dt><dd>{{ $listing->expires_at?->format('d.m.Y') ?? 'Nie ustalono' }}</dd></div>
                        </dl>

                        @if ($listing->rejection_reason)
                            <div class="listing-rejection" role="note"><strong>Powód odrzucenia</strong><p>{{ $listing->rejection_reason }}</p></div>
                        @endif

                        <div class="owner-listing-card__next">
                            <strong>Następny krok</strong>
                            <p>
                                @switch($listing->status)
                                    @case(\App\Enums\SaleListingStatus::Draft) Uzupełnij dane, a następnie wyślij ogłoszenie do moderacji. @break
                                    @case(\App\Enums\SaleListingStatus::Pending) Ogłoszenie oczekuje na decyzję administratora. @break
                                    @case(\App\Enums\SaleListingStatus::Rejected) Popraw wskazane elementy i wyślij ogłoszenie ponownie. @break
                                    @case(\App\Enums\SaleListingStatus::Approved) Po sprzedaży oznacz ofertę jako sprzedaną. @break
                                    @case(\App\Enums\SaleListingStatus::Sold) Możesz utworzyć nowy szkic na podstawie tej oferty. @break
                                    @default Ogłoszenie nie jest publiczne; możesz skopiować je jako nowy szkic.
                                @endswitch
                            </p>
                        </div>

                        <div class="owner-listing-card__actions">
                            @can('update', $listing)
                                <a href="{{ route('admin.my-listings.edit', $listing) }}" class="btn btn-primary">Edytuj</a>
                            @endcan

                            @can('markAsSold', $listing)
                                <form method="POST" action="{{ route('admin.my-listings.sold', $listing) }}" data-confirm="Oznaczyć ogłoszenie „{{ $listing->title }}” jako sprzedane?">
                                    @csrf
                                    <button class="btn btn-secondary" type="submit">Oznacz jako sprzedane</button>
                                </form>
                            @endcan

                            @if ($listing->isPubliclyVisible())
                                <a href="{{ route('listings.show', $listing) }}" class="btn btn-secondary" aria-label="Podgląd ogłoszenia {{ $listing->title }}">Podgląd</a>
                            @endif

                            @can('view', $listing)
                                <form method="POST" action="{{ route('admin.my-listings.duplicate', $listing) }}">
                                    @csrf
                                    <button class="btn btn-secondary" type="submit">Kopiuj</button>
                                </form>
                            @endcan

                            @can('delete', $listing)
                                <form method="POST" action="{{ route('admin.my-listings.destroy', $listing) }}" class="owner-listing-card__danger" data-confirm="Przenieść ogłoszenie „{{ $listing->title }}” do kosza?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger-outline" type="submit">Usuń</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        {{ $listings->links() }}
    @endif
@endsection
