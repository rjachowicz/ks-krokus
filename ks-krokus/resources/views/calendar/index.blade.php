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
    $yearOptions = range(
        min(now()->year - 5, $displayDate->year),
        max(now()->year + 5, $displayDate->year),
    );
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
            <x-form-errors />

            <input type="hidden" name="month" value="{{ $displayDate->month }}">
            <input type="hidden" name="year" value="{{ $displayDate->year }}">
            <label for="calendar-filter-type">Rodzaj
                <select id="calendar-filter-type" name="event_type"
                    @error('event_type') aria-invalid="true" aria-describedby="calendar-filter-type-error" @enderror>
                    <option value="">Wszystkie</option>
                    @foreach ($eventTypes as $value => $label)
                        <option value="{{ $value }}" @selected(request('event_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('event_type')
                    <span id="calendar-filter-type-error" class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </label>
            <label for="calendar-filter-discipline">Dyscyplina
                <select id="calendar-filter-discipline" name="discipline"
                    @error('discipline') aria-invalid="true" aria-describedby="calendar-filter-discipline-error" @enderror>
                    <option value="">Wszystkie</option>
                    @foreach ($disciplines as $value => $label)
                        <option value="{{ $value }}" @selected(request('discipline') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('discipline')
                    <span id="calendar-filter-discipline-error" class="form-error" role="alert">{{ $message }}</span>
                @enderror
            </label>
            <label for="calendar-filter-system">System
                <select id="calendar-filter-system" name="competition_system"
                    @error('competition_system') aria-invalid="true" aria-describedby="calendar-filter-system-error" @enderror>
                    <option value="">Wszystkie</option>
                    @foreach ($systems as $value => $label)
                        <option value="{{ $value }}" @selected(request('competition_system') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('competition_system')
                    <span id="calendar-filter-system-error" class="form-error" role="alert">{{ $message }}</span>
                @enderror
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
                <label class="calendar-picker__field" for="calendar-month">
                    <span class="sr-only">Miesiąc</span>
                    <select id="calendar-month" name="month"
                        @error('month') aria-invalid="true" aria-describedby="calendar-month-error" @enderror>
                        @foreach ($monthNames as $number => $name)
                            <option value="{{ $number }}" @selected($displayDate->month === $number)>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('month')
                        <span id="calendar-month-error" class="form-error" role="alert">{{ $message }}</span>
                    @enderror
                </label>
                <label class="calendar-picker__field" for="calendar-year">
                    <span class="sr-only">Rok</span>
                    <select id="calendar-year" name="year"
                        @error('year') aria-invalid="true" aria-describedby="calendar-year-error" @enderror>
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}" @selected($displayDate->year === $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                    @error('year')
                        <span id="calendar-year-error" class="form-error" role="alert">{{ $message }}</span>
                    @enderror
                </label>
                <button class="btn btn-primary" type="submit">Pokaż</button>
            </form>
        </div>

        <div class="calendar-legend" role="list" aria-label="Legenda kalendarza">
            <span role="listitem"><span class="calendar-legend__dot calendar-legend__dot--competition" aria-hidden="true"></span> Zawody</span>
            <span role="listitem"><span class="calendar-legend__dot calendar-legend__dot--training" aria-hidden="true"></span> Treningi</span>
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
                                        @if (! $event->end_at)
                                            <time datetime="{{ $event->start_at->toIso8601String() }}">{{ $event->start_at->format('H:i') }}</time>
                                        @elseif ($day->isSameDay($event->start_at) && $day->isSameDay($event->end_at))
                                            <span>
                                                <time datetime="{{ $event->start_at->toIso8601String() }}">{{ $event->start_at->format('H:i') }}</time>–<time datetime="{{ $event->end_at->toIso8601String() }}">{{ $event->end_at->format('H:i') }}</time>
                                            </span>
                                        @elseif ($day->isSameDay($event->start_at))
                                            <time datetime="{{ $event->start_at->toIso8601String() }}">od {{ $event->start_at->format('H:i') }}</time>
                                        @elseif ($event->end_at && $day->isSameDay($event->end_at))
                                            <time datetime="{{ $event->end_at->toIso8601String() }}">do {{ $event->end_at->format('H:i') }}</time>
                                        @else
                                            <span class="calendar-event__continuation">wydarzenie trwa</span>
                                        @endif
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
