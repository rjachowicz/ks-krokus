@extends('layouts.admin')

@section('title', 'Wyniki — panel KS Krokus')
@section('admin_title', 'Wyniki')

@section('content')
    <x-admin-page-header
        title="Wyniki zawodów"
        description="Rezultaty przypisane do wydarzeń, konkurencji i użytkowników."
    >
        <x-slot:actions>
            <a href="{{ route('admin.results.create') }}" class="btn btn-primary">Dodaj wynik</a>
        </x-slot:actions>
    </x-admin-page-header>

    <form method="GET" class="admin-filter panel-card">
        <label>
            Szukaj zawodnika
            <input id="result-filter-query" type="search" name="q" value="{{ request('q') }}" autocomplete="off"
                @error('q') aria-invalid="true" aria-describedby="result-filter-query-error" @enderror>
            @error('q') <span id="result-filter-query-error" class="form-error">{{ $message }}</span> @enderror
        </label>

        <label>
            Wydarzenie
            <select id="result-filter-event" name="event_id" @error('event_id') aria-invalid="true" aria-describedby="result-filter-event-error" @enderror>
                <option value="">Wszystkie</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}" @selected((string) request('event_id') === (string) $event->id)>
                        {{ $event->start_at->format('d.m.Y') }} — {{ $event->title }}
                    </option>
                @endforeach
            </select>
            @error('event_id') <span id="result-filter-event-error" class="form-error">{{ $message }}</span> @enderror
        </label>

        <label>
            Użytkownik
            <select id="result-filter-user" name="user_id" @error('user_id') aria-invalid="true" aria-describedby="result-filter-user-error" @enderror>
                <option value="">Wszyscy</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
            @error('user_id') <span id="result-filter-user-error" class="form-error">{{ $message }}</span> @enderror
        </label>

        <button type="submit" class="btn btn-primary">Filtruj</button>
        @if (request()->hasAny(['q', 'event_id', 'user_id']))
            <a href="{{ route('admin.results.index') }}" class="btn btn-secondary">Wyczyść</a>
        @endif
    </form>

    <div class="admin-table-wrap" role="region" aria-label="Lista wyników" tabindex="0">
        <table class="admin-table">
            <caption class="sr-only">Lista wyników</caption>
            <thead>
                <tr>
                    <th scope="col">Wydarzenie</th>
                    <th scope="col">Konkurencja</th>
                    <th scope="col">Zawodnik</th>
                    <th scope="col">Wynik</th>
                    <th scope="col">Miejsce</th>
                    <th scope="col">Status</th>
                    <th scope="col">Operacje</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($results as $result)
                    <tr>
                        <td data-label="Wydarzenie">
                            <strong>{{ $result->eventCompetition->event->title }}</strong><br>
                            {{ $result->eventCompetition->event->start_at->format('d.m.Y') }}
                        </td>
                        <td data-label="Konkurencja">
                            {{ $result->eventCompetition->competition->name }}<br>
                            <code>{{ $result->eventCompetition->competition->code }}</code>
                        </td>
                        <td data-label="Zawodnik">
                            <strong>{{ $result->displayName() }}</strong><br>
                            {{ $result->club_name ?: '—' }}
                        </td>
                        <td data-label="Wynik">{{ $result->score }}</td>
                        <td data-label="Miejsce">{{ $result->place ?? '—' }}</td>
                        <td data-label="Status">{{ $result->status->label() }}</td>
                        <td data-label="Operacje">
                            <div class="admin-table__actions">
                                <a href="{{ route('admin.results.edit', $result) }}" class="btn btn-secondary"
                                    aria-label="Edytuj wynik zawodnika: {{ $result->displayName() }}">Edytuj</a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.results.destroy', $result) }}"
                                    data-confirm="Usunąć wynik zawodnika „{{ $result->displayName() }}” w konkurencji „{{ $result->eventCompetition->competition->name }}”? Tej operacji nie można cofnąć."
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-outline"
                                        aria-label="Usuń wynik zawodnika: {{ $result->displayName() }}">Usuń</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">
                        <div class="empty-state">
                            {{ request()->hasAny(['q', 'event_id', 'user_id'])
                                ? 'Brak wyników spełniających wybrane kryteria.'
                                : 'Nie dodano jeszcze żadnego wyniku.' }}
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $results->links() }}
@endsection
