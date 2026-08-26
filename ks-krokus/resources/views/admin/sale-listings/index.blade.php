@extends('layouts.admin')

@section('title', 'Ogłoszenia — panel KS Krokus')
@section('admin_title', 'Ogłoszenia')

@section('content')
    <x-admin-page-header title="Moderacja ogłoszeń" description="Kolejka publikacji, aktywne oferty, historia i zgłoszenia użytkowników.">
        @if (auth()->user()->isAdmin())
            <x-slot:actions><a href="{{ route('admin.sale-listings.reports.index') }}" class="btn btn-secondary">Zgłoszenia ogłoszeń</a></x-slot:actions>
        @endif
    </x-admin-page-header>

    <nav class="listing-status-tabs" aria-label="Widoki według statusu">
        <a href="{{ route('admin.sale-listings.index') }}" @if (!request()->hasAny(['status', 'trashed'])) aria-current="page" @endif>Wszystkie</a>
        @foreach ($statuses as $value => $label)
            <a href="{{ route('admin.sale-listings.index', ['status' => $value]) }}" @if (request('status') === $value) aria-current="page" @endif>{{ $label }} <span aria-label="liczba ogłoszeń">{{ $counts[$value] ?? 0 }}</span></a>
        @endforeach
        @if (auth()->user()->isAdmin())<a href="{{ route('admin.sale-listings.index', ['trashed' => 'only']) }}" @if (request('trashed') === 'only') aria-current="page" @endif>Kosz</a>@endif
    </nav>

    <form method="GET" class="admin-filter filter-form panel-card listing-admin-filter" aria-label="Filtrowanie moderowanych ogłoszeń">
        <div class="listing-admin-filter__primary filter-form__row">
            <label for="admin-listing-status">Status
                <select id="admin-listing-status" name="status" @error('status') aria-invalid="true" aria-describedby="admin-listing-status-error" @enderror>
                    <option value="">Wszystkie statusy</option>
                    @foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach
                </select>
                @error('status') <span id="admin-listing-status-error" class="form-error">{{ $message }}</span> @enderror
            </label>
            <label for="admin-listing-author">Autor
                <select id="admin-listing-author" name="author" @error('author') aria-invalid="true" aria-describedby="admin-listing-author-error" @enderror>
                    <option value="">Wszyscy autorzy</option>
                    @foreach ($authors as $author)<option value="{{ $author->id }}" @selected((string) request('author') === (string) $author->id)>{{ $author->name }}</option>@endforeach
                </select>
                @error('author') <span id="admin-listing-author-error" class="form-error">{{ $message }}</span> @enderror
            </label>
            <label for="admin-listing-category">Kategoria
                <select id="admin-listing-category" name="category" @error('category') aria-invalid="true" aria-describedby="admin-listing-category-error" @enderror>
                    <option value="">Wszystkie kategorie</option>
                    @foreach ($categories as $value => $label)<option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>@endforeach
                </select>
                @error('category') <span id="admin-listing-category-error" class="form-error">{{ $message }}</span> @enderror
            </label>
            <div class="filter-form__actions">
                <div class="filter-form__action-group">
                    <button class="btn btn-primary" type="submit">Filtruj</button>
                </div>
            </div>
        </div>

        <details class="listing-admin-filter__more" @if (request()->hasAny(['created_from', 'created_to', 'published_from', 'published_to', 'trashed']) || $errors->any()) open @endif>
            <summary>Daty i kosz</summary>
            <div class="listing-admin-filter__advanced filter-form__row">
                @foreach ([
                    ['created_from', 'Utworzono od'],
                    ['created_to', 'Utworzono do'],
                    ['published_from', 'Opublikowano od'],
                    ['published_to', 'Opublikowano do'],
                ] as [$name, $label])
                    <label for="admin-listing-{{ str_replace('_', '-', $name) }}">{{ $label }}
                        <input id="admin-listing-{{ str_replace('_', '-', $name) }}" type="date" name="{{ $name }}" value="{{ request($name) }}"
                            @error($name) aria-invalid="true" aria-describedby="admin-listing-{{ str_replace('_', '-', $name) }}-error" @enderror>
                        @error($name) <span id="admin-listing-{{ str_replace('_', '-', $name) }}-error" class="form-error">{{ $message }}</span> @enderror
                    </label>
                @endforeach
                @if (auth()->user()->isAdmin())
                    <label for="admin-listing-trashed">Kosz
                        <select id="admin-listing-trashed" name="trashed" @error('trashed') aria-invalid="true" aria-describedby="admin-listing-trashed-error" @enderror>
                            <option value="">Bez usuniętych</option>
                            <option value="with" @selected(request('trashed') === 'with')>Razem z usuniętymi</option>
                            <option value="only" @selected(request('trashed') === 'only')>Tylko usunięte</option>
                        </select>
                        @error('trashed') <span id="admin-listing-trashed-error" class="form-error">{{ $message }}</span> @enderror
                    </label>
                @endif
                <div class="filter-form__actions">
                    <div class="filter-form__action-group">
                        <button class="btn btn-secondary" type="submit">Zastosuj zakres</button>
                    </div>
                </div>
            </div>
        </details>

        @if (request()->query())<a class="filter-form__clear" href="{{ route('admin.sale-listings.index') }}">Wyczyść wszystkie filtry</a>@endif
    </form>

    <div class="admin-table-wrap" role="region" aria-label="Lista ogłoszeń" tabindex="0">
        <table class="admin-table listing-admin-table">
            <caption class="sr-only">Ogłoszenia sprzedaży do moderacji</caption>
            <thead><tr><th scope="col">Ogłoszenie</th><th scope="col">Autor</th><th scope="col">Status</th><th scope="col">Daty</th><th scope="col">Operacje</th></tr></thead>
            <tbody>
                @forelse ($listings as $listing)
                    <tr>
                        <td data-label="Ogłoszenie">
                            <div class="listing-table-summary">
                                <div class="listing-table-summary__image">
                                    @if ($listing->primaryImage)<img src="{{ $listing->primaryImage->thumbnailUrl() }}" alt="" loading="lazy">@else<x-image-placeholder />@endif
                                </div>
                                <div><strong>{{ $listing->title }}</strong><span>{{ $listing->category->label() }} · {{ $listing->formattedPrice() }}</span>@if ($listing->pending_reports_count)<span class="status-badge status-badge--danger">Zgłoszenia: {{ $listing->pending_reports_count }}</span>@endif</div>
                            </div>
                        </td>
                        <td data-label="Autor">{{ $listing->author?->name ?? 'Usunięte konto' }}</td>
                        <td data-label="Status">
                            <div class="listing-table-badges">
                                <span class="status-badge {{ $listing->status->badgeClass() }}">{{ $listing->status->label() }}</span>
                                @if ($listing->is_hidden)<span class="status-badge status-badge--danger">Ukryte</span>@endif
                                @if ($listing->trashed())<span class="status-badge status-badge--danger">W koszu</span>@endif
                            </div>
                        </td>
                        <td data-label="Daty"><span>Utworzono: {{ $listing->created_at->format('d.m.Y') }}</span><br><span>Publikacja: {{ $listing->published_at?->format('d.m.Y H:i') ?? '—' }}</span></td>
                        <td data-label="Operacje">
                            <div class="admin-table__actions listing-admin-actions">
                                @if ($listing->trashed())
                                    @can('restore', $listing)<form method="POST" action="{{ route('admin.sale-listings.restore', $listing) }}">@csrf<button class="btn btn-primary" type="submit">Przywróć</button></form>@endcan
                                @else
                                    <a href="{{ route('admin.sale-listings.edit', $listing) }}" class="btn btn-secondary">Podgląd i edycja</a>
                                    @can('approve', $listing)<form method="POST" action="{{ route('admin.sale-listings.approve', $listing) }}" data-confirm="Zatwierdzić i opublikować ogłoszenie „{{ $listing->title }}”?">@csrf<button class="btn btn-primary" type="submit">Zatwierdź</button></form>@endcan
                                    @if ($listing->isPubliclyVisible())<a href="{{ route('listings.show', $listing) }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary" aria-label="Publiczny podgląd ogłoszenia {{ $listing->title }} — otwiera w nowej karcie">Publiczny podgląd</a>@endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state listing-table-empty"><strong>Brak ogłoszeń</strong><span>Żadne ogłoszenie nie spełnia wybranych kryteriów.</span></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $listings->links() }}
@endsection
