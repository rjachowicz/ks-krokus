@extends('layouts.app')

@section('title', 'Wyniki zawodów — KS Krokus')
@section('meta_description', 'Archiwum wyników zawodów strzeleckich KS Krokus z podziałem na ISSF, IPSC, pistolet, karabin i strzelbę.')

@section('content')
    <x-page-hero id="results-title" eyebrow="REZULTATY SPORTOWE" class="content-hero">
        <x-slot:title>
            Wyniki <span class="highlight">zawodów</span>
        </x-slot:title>

        <x-slot:description>
            <p>
                Oficjalne i wstępne rezultaty zawodów, powiązane z konkurencjami oraz profilami zawodników.
            </p>
        </x-slot:description>
    </x-page-hero>

    <section class="page-container page-section" aria-labelledby="results-list-title">
        <x-section-heading id="results-list-title" title="Archiwum wyników" meta="RESULTS_DATABASE" />

        <form method="GET" class="content-toolbar filter-form panel-card" aria-label="Filtrowanie wyników">
            <x-form-errors />

            <div class="filter-form__row">
            <label for="results-filter-query">
                Szukaj zawodów
                <input
                    id="results-filter-query"
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    autocomplete="off"
                    maxlength="100"
                    @error('q') aria-invalid="true" aria-describedby="results-filter-query-error" @enderror
                >
                @error('q')
                    <span id="results-filter-query-error" class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </label>

            <label for="results-filter-discipline">
                Dyscyplina
                <select id="results-filter-discipline" name="discipline"
                    @error('discipline') aria-invalid="true" aria-describedby="results-filter-discipline-error" @enderror>
                    <option value="">Wszystkie</option>
                    @foreach ($disciplines as $value => $label)
                        <option value="{{ $value }}" @selected(request('discipline') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('discipline')
                    <span id="results-filter-discipline-error" class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </label>

            <label for="results-filter-system">
                System
                <select id="results-filter-system" name="competition_system"
                    @error('competition_system') aria-invalid="true" aria-describedby="results-filter-system-error" @enderror>
                    <option value="">Wszystkie</option>
                    @foreach ($systems as $value => $label)
                        <option value="{{ $value }}" @selected(request('competition_system') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('competition_system')
                    <span id="results-filter-system-error" class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </label>

                <div class="filter-form__actions">
                    <div class="filter-form__action-group">
                        <button type="submit" class="btn btn-primary">Filtruj</button>
                        @if (request()->hasAny(['q', 'discipline', 'competition_system']))
                            <a href="{{ route('results.index') }}" class="btn btn-secondary">Wyczyść</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        @if ($events->isEmpty())
            <p class="empty-state">
                {{ request()->hasAny(['q', 'discipline', 'competition_system'])
                    ? 'Nie znaleziono wyników spełniających wybrane kryteria.'
                    : 'Nie opublikowano jeszcze żadnych wyników.' }}
            </p>
        @else
            <div class="results-event-grid ui-grid ui-grid--3">
                @foreach ($events as $event)
                    <x-content-card
                        class="results-event-card"
                        code="{{ $event->competition_system?->label() ?? 'SPORT' }}"
                        title="{{ $event->title }}"
                        status="{{ $event->results_count }} WYNIKÓW"
                        :href="route('results.show', $event)"
                        link-label="Otwórz wyniki →"
                    >
                        <p>
                            {{ $event->start_at->format('d.m.Y') }}<br>
                            {{ $event->location_name }}
                        </p>
                    </x-content-card>
                @endforeach
            </div>

            {{ $events->links() }}
        @endif
    </section>
@endsection
