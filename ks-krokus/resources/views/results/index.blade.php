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

    <section class="features-section" aria-labelledby="results-list-title">
        <x-section-heading id="results-list-title" title="Archiwum wyników" meta="RESULTS_DATABASE" />

        <form method="GET" class="content-toolbar" aria-label="Filtrowanie wyników">
            <label>
                Szukaj zawodów
                <input type="search" name="q" value="{{ request('q') }}">
            </label>

            <label>
                Dyscyplina
                <select name="discipline">
                    <option value="">Wszystkie</option>
                    @foreach ($disciplines as $value => $label)
                        <option value="{{ $value }}" @selected(request('discipline') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                System
                <select name="competition_system">
                    <option value="">Wszystkie</option>
                    @foreach ($systems as $value => $label)
                        <option value="{{ $value }}" @selected(request('competition_system') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </label>

            <button type="submit" class="btn btn-primary">Filtruj</button>
            @if (request()->hasAny(['q', 'discipline', 'competition_system']))
                <a href="{{ route('results.index') }}" class="btn btn-secondary">Wyczyść</a>
            @endif
        </form>

        @if ($events->isEmpty())
            <p class="content-empty">Brak opublikowanych wyników.</p>
        @else
            <div class="results-event-grid">
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
