@extends('layouts.admin')

@section('title', 'Wyniki — panel KS Krokus')
@section('admin_title', 'Wyniki')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>Wyniki zawodów</h1>
            <p>Rezultaty przypisane do wydarzeń, konkurencji i użytkowników.</p>
        </div>

        <a href="{{ route('admin.results.create') }}" class="btn btn-primary">Dodaj wynik</a>
    </header>

    <form method="GET" class="admin-filter">
        <label>
            Szukaj zawodnika
            <input type="search" name="q" value="{{ request('q') }}">
        </label>

        <label>
            Wydarzenie
            <select name="event_id">
                <option value="">Wszystkie</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}" @selected((string) request('event_id') === (string) $event->id)>
                        {{ $event->start_at->format('d.m.Y') }} — {{ $event->title }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            Użytkownik
            <select name="user_id">
                <option value="">Wszyscy</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <button type="submit" class="btn btn-primary">Filtruj</button>
        <a href="{{ route('admin.results.index') }}" class="btn btn-secondary">Wyczyść</a>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Wydarzenie</th>
                    <th>Konkurencja</th>
                    <th>Zawodnik</th>
                    <th>Wynik</th>
                    <th>Miejsce</th>
                    <th>Status</th>
                    <th>Operacje</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($results as $result)
                    <tr>
                        <td>
                            <strong>{{ $result->eventCompetition->event->title }}</strong><br>
                            {{ $result->eventCompetition->event->start_at->format('d.m.Y') }}
                        </td>
                        <td>
                            {{ $result->eventCompetition->competition->name }}<br>
                            <code>{{ $result->eventCompetition->competition->code }}</code>
                        </td>
                        <td>
                            <strong>{{ $result->displayName() }}</strong><br>
                            {{ $result->club_name ?: '—' }}
                        </td>
                        <td>{{ $result->score }}</td>
                        <td>{{ $result->place ?? '—' }}</td>
                        <td>{{ $result->status->label() }}</td>
                        <td>
                            <div class="admin-table__actions">
                                <a href="{{ route('admin.results.edit', $result) }}" class="btn btn-secondary">Edytuj</a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.results.destroy', $result) }}"
                                    data-confirm="Usunąć wynik? Tej operacji nie można cofnąć."
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-outline">Usuń</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">Brak wyników.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $results->links() }}
@endsection
