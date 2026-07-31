@extends('layouts.admin')

@section('title', 'Kalendarz — panel KS Krokus')
@section('admin_title', 'Kalendarz')

@section('content')
    <x-admin-page-header
        title="Wydarzenia"
        description="Zawody, treningi, terminy, miejsca oraz przypisane konkurencje."
    >
        <x-slot:actions>
            <a href="{{ route('admin.events.create') }}" class="btn btn-primary">Dodaj wydarzenie</a>
        </x-slot:actions>
    </x-admin-page-header>

    <form method="GET" class="admin-filter">
        <label>
            Szukaj
            <input id="event-filter-query" type="search" name="q" value="{{ request('q') }}" placeholder="Nazwa lub miejsce" autocomplete="off"
                @error('q') aria-invalid="true" aria-describedby="event-filter-query-error" @enderror>
            @error('q') <span id="event-filter-query-error" class="form-error">{{ $message }}</span> @enderror
        </label>

        <label>
            Rodzaj
            <select id="event-filter-type" name="event_type" @error('event_type') aria-invalid="true" aria-describedby="event-filter-type-error" @enderror>
                <option value="">Wszystkie</option>
                @foreach ($eventTypes as $value => $label)
                    <option value="{{ $value }}" @selected(request('event_type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('event_type') <span id="event-filter-type-error" class="form-error">{{ $message }}</span> @enderror
        </label>

        <label>
            Status
            <select id="event-filter-status" name="status" @error('status') aria-invalid="true" aria-describedby="event-filter-status-error" @enderror>
                <option value="">Wszystkie</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <span id="event-filter-status-error" class="form-error">{{ $message }}</span> @enderror
        </label>

        <button type="submit" class="btn btn-primary">Filtruj</button>
        @if (request()->hasAny(['q', 'event_type', 'status']))
            <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">Wyczyść</a>
        @endif
    </form>

    <div class="admin-table-wrap" role="region" aria-label="Lista wydarzeń" tabindex="0">
        <table class="admin-table">
            <caption class="sr-only">Lista wydarzeń</caption>
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
                        <td data-label="Termin">{{ $event->start_at->format('d.m.Y H:i') }}</td>
                        <td data-label="Wydarzenie">
                            <strong>{{ $event->title }}</strong><br>
                            {{ $event->location_name }}
                        </td>
                        <td data-label="Rodzaj">{{ $event->event_type->label() }}</td>
                        <td data-label="Konkurencje">{{ $event->event_competitions_count }}</td>
                        <td data-label="Wyniki">{{ $event->results_count }}</td>
                        <td data-label="Status">
                            <span class="admin-badge {{ $event->status === \App\Enums\PublicationStatus::Published ? 'admin-badge--success' : 'admin-badge--warning' }}">
                                {{ $event->status->label() }}
                            </span>
                            @if (! $event->is_public)
                                <span class="admin-badge admin-badge--danger">Prywatne</span>
                            @endif
                        </td>
                        <td data-label="Operacje">
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
                                    data-confirm="Przenieść wydarzenie do kosza? Powiązane wyniki pozostaną w bazie."
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-outline">Usuń</button>
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
