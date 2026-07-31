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
    </x-page-hero>

    <section class="features-section" aria-label="Tabela wyników">
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
                                <th>Miejsce</th>
                                <th>Zawodnik</th>
                                <th>Klub</th>
                                <th>Kategoria</th>
                                <th>Wynik</th>
                                <th>Klasyfikacja</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($eventCompetition->results as $result)
                                <tr>
                                    <td>{{ $result->place ?? '—' }}</td>
                                    <td>{{ $result->displayName() }}</td>
                                    <td>{{ $result->club_name ?: '—' }}</td>
                                    <td>{{ $result->category ?: '—' }}</td>
                                    <td><strong>{{ $result->score }}</strong></td>
                                    <td>{{ $result->classification ?: '—' }}</td>
                                    <td>{{ $result->status->label() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        @empty
            <p class="content-empty">Brak wyników dla tego wydarzenia.</p>
        @endforelse
    </section>
@endsection
