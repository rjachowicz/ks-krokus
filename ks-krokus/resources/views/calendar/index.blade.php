@extends('layouts.app')

@section('title', 'Kalendarz zawodów i treningów — KS Krokus')
@section('meta_description', 'Kalendarz zawodów, treningów i wydarzeń strzeleckich KS Krokus z podziałem na ISSF, IPSC, pistolet, karabin i strzelbę.')

@section('content')
    <x-page-hero id="calendar-title" eyebrow="TERMINARZ SPORTOWY" class="content-hero">
        <x-slot:title>
            Kalendarz <span class="highlight">zawodów i treningów</span>
        </x-slot:title>

        <x-slot:description>
            <p>
                Terminy, miejsca i konkurencje wydarzeń klubowych z możliwością filtrowania według rodzaju,
                dyscypliny i systemu sportowego.
            </p>
        </x-slot:description>
    </x-page-hero>

    <section class="features-section" aria-labelledby="calendar-list-title">
        <x-section-heading id="calendar-list-title" title="Wydarzenia" meta="SPORT_EVENTS" />

        <form method="GET" class="content-toolbar">
            <label>
                Rodzaj
                <select name="event_type">
                    <option value="">Wszystkie</option>
                    @foreach ($eventTypes as $value => $label)
                        <option value="{{ $value }}" @selected(request('event_type') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
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

            <label class="form-check">
                <input type="checkbox" name="past" value="1" @checked(request()->boolean('past'))>
                Pokaż archiwalne
            </label>

            <button type="submit" class="btn btn-primary">Filtruj</button>
            <a href="{{ route('calendar.index') }}" class="btn btn-secondary">Wyczyść</a>
        </form>

        @if ($events->isEmpty())
            <div class="content-empty">Brak wydarzeń spełniających wybrane kryteria.</div>
        @else
            <div class="event-grid">
                @foreach ($events as $event)
                    <x-content-card class="event-card">
                        <div class="event-card__meta">
                            <span>{{ $event->event_type->label() }}</span>
                            <span>{{ $event->start_at->format('d.m.Y H:i') }}</span>
                        </div>

                        <h3>{{ $event->title }}</h3>

                        <p>
                            <strong>{{ $event->location_name }}</strong>
                            @if ($event->address)
                                <br>{{ $event->address }}
                            @endif
                        </p>

                        @if ($event->description)
                            <p>{{ \Illuminate\Support\Str::limit($event->description, 160) }}</p>
                        @endif

                        <x-slot:footer>
                            <span class="card-status">
                                {{ $event->competition_system?->label() }}
                                {{ $event->discipline?->label() }}
                            </span>
                            <a href="{{ route('calendar.show', $event) }}" class="card-link">
                                Szczegóły →
                            </a>
                        </x-slot:footer>
                    </x-content-card>
                @endforeach
            </div>

            {{ $events->links() }}
        @endif
    </section>
@endsection
