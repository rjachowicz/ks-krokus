@extends('layouts.app')

@section('title', 'Wyniki: '.$sportEvent->title.' — KS Krokus')
@section('meta_description', 'Wyniki zawodów '.$sportEvent->title.' organizowanych przez KS Krokus.')

@php
    $resultCompetitions = $sportEvent->eventCompetitions
        ->filter(fn ($eventCompetition) => $eventCompetition->results->isNotEmpty());
@endphp

@section('content')
    <x-page-hero id="result-event-title" eyebrow="WYNIKI ZAWODÓW" class="content-hero">
        <x-slot:title>{{ $sportEvent->title }}</x-slot:title>

        <x-slot:description>
            <p>
                {{ $sportEvent->start_at->format('d.m.Y') }} —
                {{ $sportEvent->location_name }}
            </p>
        </x-slot:description>

        <x-slot:actions>
            <a href="{{ route('results.index') }}" class="btn btn-secondary">← Wróć do wyników</a>
        </x-slot:actions>
    </x-page-hero>

    <section class="page-container page-section" aria-label="Tabela wyników">
        <form method="GET" action="{{ route('results.show', $sportEvent) }}" class="content-toolbar filter-form panel-card" aria-label="Wyszukiwanie zawodnika">
            <x-form-errors />

            <div class="filter-form__row">
                <label for="result-participant-query">
                    Imię i nazwisko zawodnika
                    <input
                        id="result-participant-query"
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        maxlength="100"
                        autocomplete="off"
                        @error('q') aria-invalid="true" aria-describedby="result-participant-query-error" @enderror
                    >
                    @error('q')
                        <span id="result-participant-query-error" class="form-error" role="alert">{{ $message }}</span>
                    @enderror
                </label>

                <div class="filter-form__actions">
                    <div class="filter-form__action-group">
                        <button type="submit" class="btn btn-primary">Szukaj</button>
                        @if (request()->filled('q'))
                            <a href="{{ route('results.show', $sportEvent) }}" class="btn btn-secondary">Wyczyść</a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        @if ($search !== null)
            <p class="results-match-count" role="status">
                Liczba dopasowanych wyników: <strong>{{ $matchedResultsCount }}</strong>
            </p>
        @endif

        @forelse ($resultCompetitions as $eventCompetition)
            <article class="result-section">
                <h2>
                    {{ $eventCompetition->competition->name }}
                    <small>
                        {{ $eventCompetition->competition->competition_system->label() }} /
                        {{ $eventCompetition->competition->discipline->label() }}
                    </small>
                </h2>

                <div
                    class="results-table-wrap"
                    role="region"
                    aria-label="Wyniki konkurencji {{ $eventCompetition->competition->name }}"
                    tabindex="0"
                >
                    <table class="results-table">
                        <caption class="sr-only">Wyniki konkurencji {{ $eventCompetition->competition->name }}</caption>
                        <thead>
                            <tr>
                                <th scope="col">Miejsce</th>
                                <th scope="col">Zawodnik</th>
                                <th scope="col">Klub</th>
                                <th scope="col">Kategoria</th>
                                <th scope="col">Wynik</th>
                                <th scope="col">Klasyfikacja</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($eventCompetition->results as $result)
                                <tr>
                                    <td>{{ $result->place ?? '—' }}</td>
                                    <td>{{ $result->displayName() }}</td>
                                    <td>{{ $result->club_name ?: '—' }}</td>
                                    <td>{{ $result->categoryLabel() ?: '—' }}</td>
                                    <td><strong>{{ $result->score }}</strong></td>
                                    <td>{{ $result->classificationLabel() ?: '—' }}</td>
                                    <td>{{ $result->status->label() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        @empty
            <p class="empty-state">
                {{ $search !== null
                    ? 'Nie znaleziono wyników dla podanego zawodnika.'
                    : 'Brak wyników dla tego wydarzenia.' }}
            </p>
        @endforelse
    </section>
@endsection
