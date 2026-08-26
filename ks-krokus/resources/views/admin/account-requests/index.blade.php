@extends('layouts.admin')

@section('title', 'Wnioski o konto — panel KS Krokus')
@section('admin_title', 'Wnioski o konto')

@section('content')
    <x-admin-page-header title="Wnioski o konto" description="Weryfikacja członkostwa przed utworzeniem konta i wysłaniem linku ustawienia hasła." />

    <nav class="listing-status-tabs" aria-label="Wnioski według statusu">
        <a href="{{ route('admin.account-requests.index') }}" @if (!request('status')) aria-current="page" @endif>Wszystkie <span>{{ $accountRequests->total() }}</span></a>
        @foreach ($statuses as $value => $label)
            <a href="{{ route('admin.account-requests.index', ['status' => $value]) }}" @if (request('status') === $value) aria-current="page" @endif>{{ $label }} <span aria-label="liczba wniosków">{{ $counts[$value] ?? 0 }}</span></a>
        @endforeach
    </nav>

    <form method="GET" class="admin-filter filter-form panel-card account-request-filter" aria-label="Filtrowanie wniosków o konto">
        <div class="filter-form__row">
        <label for="account-request-query">Szukaj
            <input id="account-request-query" type="search" name="q" value="{{ request('q') }}" maxlength="100" placeholder="Imię, e-mail lub licencja"
                @error('q') aria-invalid="true" aria-describedby="account-request-query-error" @enderror>
            @error('q')<span id="account-request-query-error" class="form-error">{{ $message }}</span>@enderror
        </label>

        <label for="account-request-status">Status
            <select id="account-request-status" name="status" @error('status') aria-invalid="true" aria-describedby="account-request-status-error" @enderror>
                <option value="">Wszystkie statusy</option>
                @foreach ($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach
            </select>
            @error('status')<span id="account-request-status-error" class="form-error">{{ $message }}</span>@enderror
        </label>

        <label for="account-request-discipline">Dyscyplina
            <select id="account-request-discipline" name="discipline" @error('discipline') aria-invalid="true" aria-describedby="account-request-discipline-error" @enderror>
                <option value="">Wszystkie dyscypliny</option>
                @foreach ($disciplines as $value => $label)<option value="{{ $value }}" @selected(request('discipline') === $value)>{{ $label }}</option>@endforeach
            </select>
            @error('discipline')<span id="account-request-discipline-error" class="form-error">{{ $message }}</span>@enderror
        </label>

        <label for="account-request-created-from">Złożono od
            <input id="account-request-created-from" type="date" name="created_from" value="{{ request('created_from') }}"
                @error('created_from') aria-invalid="true" aria-describedby="account-request-created-from-error" @enderror>
            @error('created_from')<span id="account-request-created-from-error" class="form-error">{{ $message }}</span>@enderror
        </label>

        <label for="account-request-created-to">Złożono do
            <input id="account-request-created-to" type="date" name="created_to" value="{{ request('created_to') }}"
                @error('created_to') aria-invalid="true" aria-describedby="account-request-created-to-error" @enderror>
            @error('created_to')<span id="account-request-created-to-error" class="form-error">{{ $message }}</span>@enderror
        </label>

            <div class="filter-form__actions">
                <div class="filter-form__action-group">
                    <button type="submit" class="btn btn-primary">Filtruj</button>
                    @if (request()->query())<a href="{{ route('admin.account-requests.index') }}" class="btn btn-secondary">Wyczyść</a>@endif
                </div>
            </div>
        </div>
    </form>

    <div class="admin-table-wrap" role="region" aria-label="Lista wniosków o konto" tabindex="0">
        <table class="admin-table">
            <caption class="sr-only">Wnioski członków klubu o utworzenie konta</caption>
            <thead><tr><th scope="col">Wnioskodawca</th><th scope="col">Licencja PZSS</th><th scope="col">Status</th><th scope="col">Złożono</th><th scope="col">Operacje</th></tr></thead>
            <tbody>
                @forelse ($accountRequests as $item)
                    <tr>
                        <td data-label="Wnioskodawca"><strong>{{ $item->fullName() }}</strong><br><span>{{ $item->email }}</span></td>
                        <td data-label="Licencja PZSS">{{ $item->pzss_license_number }}</td>
                        <td data-label="Status"><span class="status-badge {{ $item->status->badgeClass() }}">{{ $item->status->label() }}</span></td>
                        <td data-label="Złożono"><time datetime="{{ $item->created_at->toIso8601String() }}">{{ $item->created_at->format('d.m.Y H:i') }}</time></td>
                        <td data-label="Operacje"><div class="admin-table__actions"><a class="btn btn-secondary" href="{{ route('admin.account-requests.show', $item) }}">Szczegóły i decyzja</a></div></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><p class="empty-state">Brak wniosków spełniających wybrane kryteria.</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $accountRequests->links() }}
@endsection
