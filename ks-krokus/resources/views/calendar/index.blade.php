@extends('layouts.app')

@section('title', 'Kalendarz zawodów i treningów — KS Krokus')
@section('meta_description', 'Miesięczny kalendarz zawodów, treningów i wydarzeń strzeleckich KS Krokus.')

@php
    $filterQuery = request()->only(['event_type', 'discipline', 'competition_system']);
    $monthNames = [
        1 => 'Styczeń', 2 => 'Luty', 3 => 'Marzec', 4 => 'Kwiecień',
        5 => 'Maj', 6 => 'Czerwiec', 7 => 'Lipiec', 8 => 'Sierpień',
        9 => 'Wrzesień', 10 => 'Październik', 11 => 'Listopad', 12 => 'Grudzień',
    ];
@endphp

@section('content')
    <x-page-hero id="calendar-title" eyebrow="TERMINARZ SPORTOWY" class="content-hero">
        <x-slot:title>Kalendarz <span class="highlight">zawodów i treningów</span></x-slot:title>
        <x-slot:description>
            <p>Terminy zawodów, treningów i pozostałych wydarzeń klubowych w jednym miejscu.</p>
        </x-slot:description>
    </x-page-hero>

    <section class="features-section calendar-section" aria-labelledby="calendar-month-title">
        <form method="GET" class="content-toolbar calendar-filters" aria-label="Filtry kalendarza">
            <input type="hidden" name="month" value="{{ $displayDate->month }}">
            <input type="hidden" name="year" value="{{ $displayDate->year }}">
            <label>Rodzaj
                <select name="event_type">
                    <option value="">Wszystkie</option>
                    @foreach ($eventTypes as $value => $label)
                        <option value="{{ $value }}" @selected(request('event_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>Dyscyplina
                <select name="discipline">
                    <option value="">Wszystkie</option>
                    @foreach ($disciplines as $value => $label)
                        <option value="{{ $value }}" @selected(request('discipline') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>System
                <select name="competition_system">
                    <option value="">Wszystkie</option>
                    @foreach ($systems as $value => $label)
                        <option value="{{ $value }}" @selected(request('competition_system') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="btn btn-primary">Filtruj</button>
            @if (request()->hasAny(['event_type', 'discipline', 'competition_system']))
                <a href="{{ route('calendar.index', ['month' => $displayDate->month, 'year' => $displayDate->year]) }}" class="btn btn-secondary">Wyczyść</a>
            @endif
        </form>

        <div class="calendar-toolbar">
            <div class="calendar-navigation">
                <a class="btn btn-secondary" aria-label="Poprzedni miesiąc" href="{{ route('calendar.index', [...$filterQuery, 'month' => $previousMonth->month, 'year' => $previousMonth->year]) }}">←</a>
                <a class="btn btn-secondary" href="{{ route('calendar.index', $filterQuery) }}">Dzisiaj</a>
                <a class="btn btn-secondary" aria-label="Następny miesiąc" href="{{ route('calendar.index', [...$filterQuery, 'month' => $nextMonth->month, 'year' => $nextMonth->year]) }}">→</a>
            </div>

            <h2 id="calendar-month-title">{{ $monthNames[$displayDate->month] }} {{ $displayDate->year }}</h2>

            <form method="GET" class="calendar-picker">
                @foreach ($filterQuery as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach
                <label class="sr-only" for="calendar-month">Miesiąc</label>
                <select id="calendar-month" name="month">
                    @foreach ($monthNames as $number => $name)
                        <option value="{{ $number }}" @selected($displayDate->month === $number)>{{ $name }}</option>
                    @endforeach
                </select>
                <label class="sr-only" for="calendar-year">Rok</label>
                <select id="calendar-year" name="year">
                    @foreach (range(now()->year - 5, now()->year + 5) as $year)
                        <option value="{{ $year }}" @selected($displayDate->year === $year)>{{ $year }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary" type="submit">Pokaż</button>
            </form>
        </div>

        <div class="calendar-legend" aria-label="Legenda">
            <span><span class="calendar-legend__dot calendar-legend__dot--competition" aria-hidden="true"></span> Zawody</span>
            <span><span class="calendar-legend__dot calendar-legend__dot--training" aria-hidden="true"></span> Treningi</span>
        </div>

        @if ($events->isEmpty())
            <p class="content-empty">Brak wydarzeń spełniających wybrane kryteria w tym miesiącu.</p>
        @else
            <div class="month-calendar">
                <div class="month-calendar__weekdays" aria-hidden="true">
                    @foreach (['Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', 'Niedziela'] as $weekday)
                        <span>{{ $weekday }}</span>
                    @endforeach
                </div>
                <div class="month-calendar__grid">
                    @foreach ($days as $day)
                        @php($dayEvents = $eventsByDate->get($day->toDateString(), collect()))
                        <section class="calendar-day {{ $day->month !== $displayDate->month ? 'calendar-day--outside' : '' }} {{ $day->isToday() ? 'calendar-day--today' : '' }}" aria-label="{{ $day->translatedFormat('l, j F Y') }}">
                            <header>
                                <span class="calendar-day__weekday">{{ ucfirst($day->translatedFormat('D')) }}</span>
                                <time datetime="{{ $day->toDateString() }}">{{ $day->day }}</time>
                            </header>
                            <div class="calendar-day__events">
                                @foreach ($dayEvents as $event)
                                    <a class="calendar-event calendar-event--{{ $event->event_type->value }}" href="{{ route('calendar.show', $event) }}">
                                        <time datetime="{{ $event->start_at->toIso8601String() }}">{{ $event->start_at->format('H:i') }}</time>
                                        <strong>{{ $event->title }}</strong>
                                        @if ($event->location_name)<span>{{ $event->location_name }}</span>@endif
                                    </a>
                                @endforeach
                                @if ($dayEvents->isEmpty())
                                    <span class="calendar-day__empty">Brak wydarzeń</span>
                                @endif
                            </div>
                        </section>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection
