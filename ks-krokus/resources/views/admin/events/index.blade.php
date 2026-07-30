@extends('layouts.admin')

@section('title', 'Kalendarz — panel KS Krokus')
@section('admin_title', 'Kalendarz')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>Wydarzenia</h1>
            <p>Zawody, treningi, terminy, miejsca oraz przypisane konkurencje.</p>
        </div>

        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">Dodaj wydarzenie</a>
    </header>

    <form method="GET" class="admin-filter">
        <label>
            Szukaj
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Nazwa lub miejsce">
        </label>

        <label>
            Rodzaj
            <select name="event_type">
                <option value="">Wszystkie</option>
                @foreach ($eventTypes as $value => $label)
                    <option value="{{ $value }}" @selected(request('event_type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label>
            Status
            <select name="status">
                <option value="">Wszystkie</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <button type="submit" class="btn btn-primary">Filtruj</button>
        <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">Wyczyść</a>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Termin</th>
                    <th>Wydarzenie</th>
                    <th>Rodzaj</th>
                    <th>Konkurencje</th>
                    <th>Wyniki</th>
                    <th>Status</th>
                    <th>Operacje</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($events as $event)
                    <tr>
                        <td>{{ $event->start_at->format('d.m.Y H:i') }}</td>
                        <td>
                            <strong>{{ $event->title }}</strong><br>
                            {{ $event->location_name }}
                        </td>
                        <td>{{ $event->event_type->label() }}</td>
                        <td>{{ $event->event_competitions_count }}</td>
                        <td>{{ $event->results_count }}</td>
                        <td>
                            <span class="admin-badge {{ $event->status === \App\Enums\PublicationStatus::Published ? 'admin-badge--success' : 'admin-badge--warning' }}">
                                {{ $event->status->label() }}
                            </span>
                            @if (! $event->is_public)
                                <span class="admin-badge admin-badge--danger">Prywatne</span>
                            @endif
                        </td>
                        <td>
                            <div class="admin-table__actions">
                                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-secondary">Edytuj</a>

                                @if ($event->is_public && $event->status === \App\Enums\PublicationStatus::Published)
                                    <a
                                        href="{{ route('calendar.show', $event) }}"
                                        class="btn btn-secondary"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        Podgląd
                                    </a>
                                @endif

                                <form
                                    method="POST"
                                    action="{{ route('admin.events.destroy', $event) }}"
                                    onsubmit="return confirm('Usunąć wydarzenie? Powiązane wyniki pozostaną w bazie do czasu trwałego usunięcia rekordu.');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary">Usuń</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">Brak wydarzeń.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $events->links() }}
@endsection
