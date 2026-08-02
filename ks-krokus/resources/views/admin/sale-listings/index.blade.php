@extends('layouts.admin')

@section('title', 'Ogłoszenia — panel KS Krokus')
@section('admin_title', 'Ogłoszenia')

@section('content')
    <x-admin-page-header title="Moderacja ogłoszeń" description="Kolejka publikacji, aktywne oferty, historia i zgłoszenia użytkowników.">
        @if (auth()->user()->isAdmin())
            <x-slot:actions><a href="{{ route('admin.sale-listings.reports.index') }}" class="btn btn-secondary">Zgłoszenia ogłoszeń</a></x-slot:actions>
        @endif
    </x-admin-page-header>

    <nav class="listing-status-tabs" aria-label="Status ogłoszeń">
        <a href="{{ route('admin.sale-listings.index') }}" @if (!request()->hasAny(['status', 'trashed'])) aria-current="page" @endif>Wszystkie</a>
        @foreach ($statuses as $value => $label)
            <a href="{{ route('admin.sale-listings.index', ['status' => $value]) }}" @if (request('status') === $value) aria-current="page" @endif>{{ $label }} <span>{{ $counts[$value] ?? 0 }}</span></a>
        @endforeach
        @if (auth()->user()->isAdmin())<a href="{{ route('admin.sale-listings.index', ['trashed' => 'only']) }}" @if (request('trashed') === 'only') aria-current="page" @endif>Kosz</a>@endif
    </nav>

    <form method="GET" class="admin-filter listing-admin-filter">
        <label>Status<select name="status"><option value="">Wszystkie</option>@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>
        <label>Autor<select name="author"><option value="">Wszyscy</option>@foreach ($authors as $author)<option value="{{ $author->id }}" @selected((string) request('author') === (string) $author->id)>{{ $author->name }}</option>@endforeach</select></label>
        <label>Kategoria<select name="category"><option value="">Wszystkie</option>@foreach ($categories as $value => $label)<option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>@endforeach</select></label>
        <label>Utworzono od<input type="date" name="created_from" value="{{ request('created_from') }}"></label>
        <label>Utworzono do<input type="date" name="created_to" value="{{ request('created_to') }}"></label>
        <label>Opublikowano od<input type="date" name="published_from" value="{{ request('published_from') }}"></label>
        <label>Opublikowano do<input type="date" name="published_to" value="{{ request('published_to') }}"></label>
        @if (auth()->user()->isAdmin())<label>Kosz<select name="trashed"><option value="">Bez usuniętych</option><option value="with" @selected(request('trashed') === 'with')>Razem z usuniętymi</option><option value="only" @selected(request('trashed') === 'only')>Tylko usunięte</option></select></label>@endif
        <button class="btn btn-primary" type="submit">Filtruj</button>
        @if (request()->query())<a class="btn btn-secondary" href="{{ route('admin.sale-listings.index') }}">Wyczyść</a>@endif
    </form>

    <div class="admin-table-wrap" role="region" aria-label="Lista ogłoszeń" tabindex="0">
        <table class="admin-table listing-admin-table">
            <caption class="sr-only">Ogłoszenia sprzedaży do moderacji</caption>
            <thead><tr><th scope="col">Ogłoszenie</th><th scope="col">Autor</th><th scope="col">Status</th><th scope="col">Daty</th><th scope="col">Operacje</th></tr></thead>
            <tbody>
                @forelse ($listings as $listing)
                    <tr>
                        <td data-label="Ogłoszenie"><strong>{{ $listing->title }}</strong><br>{{ $listing->category->label() }} · {{ $listing->formattedPrice() }}@if ($listing->pending_reports_count)<br><span class="admin-badge admin-badge--danger">Zgłoszenia: {{ $listing->pending_reports_count }}</span>@endif</td>
                        <td data-label="Autor">{{ $listing->author?->name ?? 'Usunięte konto' }}</td>
                        <td data-label="Status"><span class="admin-badge {{ $listing->status->badgeClass() }}">{{ $listing->status->label() }}</span>@if ($listing->is_hidden)<br><span class="admin-badge admin-badge--danger">Ukryte</span>@endif @if ($listing->trashed())<br><span class="admin-badge admin-badge--danger">W koszu</span>@endif</td>
                        <td data-label="Daty">Utworzono: {{ $listing->created_at->format('d.m.Y') }}<br>Publikacja: {{ $listing->published_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td data-label="Operacje"><div class="admin-table__actions">
                            @if ($listing->trashed())
                                @can('restore', $listing)<form method="POST" action="{{ route('admin.sale-listings.restore', $listing) }}">@csrf<button class="btn btn-primary" type="submit">Przywróć</button></form>@endcan
                            @else
                                <a href="{{ route('admin.sale-listings.edit', $listing) }}" class="btn btn-secondary">Podgląd i edycja</a>
                                @can('approve', $listing)<form method="POST" action="{{ route('admin.sale-listings.approve', $listing) }}" data-confirm="Zatwierdzić i opublikować ogłoszenie „{{ $listing->title }}”?">@csrf<button class="btn btn-primary" type="submit">Zatwierdź</button></form>@endcan
                                @if ($listing->isPubliclyVisible())<a href="{{ route('listings.show', $listing) }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary" aria-label="Publiczny podgląd ogłoszenia {{ $listing->title }} — otwiera w nowej karcie">Publiczny podgląd</a>@endif
                            @endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5">Brak ogłoszeń spełniających wybrane kryteria.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $listings->links() }}
@endsection
