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

    <form method="GET" class="admin-filter filter-form panel-card">
        <div class="filter-form__row">
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

            <div class="filter-form__actions">
                <div class="filter-form__action-group">
                    <button type="submit" class="btn btn-primary">Filtruj</button>
                    @if (request()->hasAny(['q', 'event_type', 'status']))
                        <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">Wyczyść</a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <div class="admin-table-wrap" role="region" aria-label="Lista wydarzeń" tabindex="0">
        <table class="admin-table">
            <caption class="sr-only">Lista wydarzeń</caption>
            <thead>
                <tr>
                    <th scope="col">Termin</th>
                    <th scope="col">Wydarzenie</th>
                    <th scope="col">Rodzaj</th>
                    <th scope="col">Konkurencje</th>
                    <th scope="col">Wyniki</th>
                    <th scope="col">Status</th>
                    <th scope="col">Operacje</th>
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
                                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-secondary"
                                    aria-label="Edytuj wydarzenie: {{ $event->title }}">Edytuj</a>

                                @if ($event->is_public && $event->status === \App\Enums\PublicationStatus::Published)
                                    <a
                                        href="{{ route('calendar.show', $event) }}"
                                        class="btn btn-secondary"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label="Podgląd wydarzenia: {{ $event->title }} — otwiera w nowej karcie"
                                    >
                                        Podgląd
                                    </a>
                                @endif

                                <form
                                    method="POST"
                                    action="{{ route('admin.events.destroy', $event) }}"
                                    data-confirm="Przenieść wydarzenie „{{ $event->title }}” do kosza? Zniknie z kalendarza, a powiązane wyniki pozostaną w bazie."
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-outline"
                                        aria-label="Usuń wydarzenie: {{ $event->title }}">Usuń</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">
                        <div class="empty-state">
                            {{ request()->hasAny(['q', 'event_type', 'status'])
                                ? 'Brak wydarzeń spełniających wybrane kryteria.'
                                : 'Nie dodano jeszcze żadnego wydarzenia.' }}
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $events->links() }}
@endsection
