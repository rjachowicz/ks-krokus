@extends('layouts.admin')

@section('title', 'Moje ogłoszenia — panel KS Krokus')
@section('admin_title', 'Moje ogłoszenia')

@section('content')
    <x-admin-page-header title="Moje ogłoszenia" description="Szkice, moderacja i opublikowane oferty w jednym miejscu.">
        <x-slot:actions><a href="{{ route('admin.my-listings.create') }}" class="btn btn-primary">Dodaj ogłoszenie</a></x-slot:actions>
    </x-admin-page-header>

    <form method="GET" class="admin-filter">
        <label>Status
            <select name="status"><option value="">Wszystkie</option>@foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select>
        </label>
        <button type="submit" class="btn btn-primary">Filtruj</button>
        @if (request('status'))<a href="{{ route('admin.my-listings.index') }}" class="btn btn-secondary">Wyczyść</a>@endif
    </form>

    <div class="admin-table-wrap" role="region" aria-label="Moje ogłoszenia" tabindex="0">
        <table class="admin-table listing-admin-table">
            <caption class="sr-only">Lista moich ogłoszeń sprzedaży</caption>
            <thead><tr><th scope="col">Ogłoszenie</th><th scope="col">Status i terminy</th><th scope="col">Następne działanie</th><th scope="col">Operacje</th></tr></thead>
            <tbody>
                @forelse ($listings as $listing)
                    <tr>
                        <td data-label="Ogłoszenie"><strong>{{ $listing->title }}</strong><br><span>{{ $listing->category->label() }} · {{ $listing->formattedPrice() }}</span></td>
                        <td data-label="Status i terminy">
                            <span class="admin-badge {{ $listing->status->badgeClass() }}">{{ $listing->status->label() }}</span>
                            @if ($listing->submitted_at)<br>Wysłano: {{ $listing->submitted_at->format('d.m.Y H:i') }}@endif
                            @if ($listing->expires_at)<br>Wygasa: {{ $listing->expires_at->format('d.m.Y') }}@endif
                            @if ($listing->rejection_reason)<div class="listing-rejection"><strong>Powód:</strong> {{ $listing->rejection_reason }}</div>@endif
                        </td>
                        <td data-label="Następne działanie">
                            @switch($listing->status)
                                @case(\App\Enums\SaleListingStatus::Draft) Uzupełnij dane i wyślij do moderacji. @break
                                @case(\App\Enums\SaleListingStatus::Pending) Poczekaj na decyzję administratora. @break
                                @case(\App\Enums\SaleListingStatus::Rejected) Popraw ogłoszenie i wyślij je ponownie. @break
                                @case(\App\Enums\SaleListingStatus::Approved) Po sprzedaży oznacz ofertę jako sprzedaną. @break
                                @case(\App\Enums\SaleListingStatus::Sold) Możesz skopiować ofertę jako nowy szkic. @break
                                @default Ogłoszenie nie jest już publiczne.
                            @endswitch
                        </td>
                        <td data-label="Operacje">
                            <div class="admin-table__actions">
                                @can('update', $listing)<a href="{{ route('admin.my-listings.edit', $listing) }}" class="btn btn-secondary">Edytuj</a>@endcan
                                @can('submit', $listing)<form method="POST" action="{{ route('admin.my-listings.submit', $listing) }}">@csrf<button class="btn btn-primary" type="submit">Wyślij</button></form>@endcan
                                @can('markAsSold', $listing)<form method="POST" action="{{ route('admin.my-listings.sold', $listing) }}" data-confirm="Oznaczyć ogłoszenie „{{ $listing->title }}” jako sprzedane?">@csrf<button class="btn btn-secondary" type="submit">Sprzedane</button></form>@endcan
                                @if ($listing->isPubliclyVisible())<a href="{{ route('listings.show', $listing) }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary" aria-label="Podgląd ogłoszenia {{ $listing->title }} — otwiera w nowej karcie">Podgląd</a>@endif
                                <form method="POST" action="{{ route('admin.my-listings.duplicate', $listing) }}">@csrf<button class="btn btn-secondary" type="submit">Kopiuj jako nowe</button></form>
                                @can('delete', $listing)<form method="POST" action="{{ route('admin.my-listings.destroy', $listing) }}" data-confirm="Przenieść ogłoszenie „{{ $listing->title }}” do kosza?">@csrf @method('DELETE')<button class="btn btn-danger-outline" type="submit">Usuń</button></form>@endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">Nie masz jeszcze ogłoszeń w tej kategorii.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $listings->links() }}
@endsection
