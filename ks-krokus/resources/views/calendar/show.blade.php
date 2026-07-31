@extends('layouts.app')

@section('title', $sportEvent->title.' — kalendarz KS Krokus')
@section('meta_description', \Illuminate\Support\Str::limit($sportEvent->description ?: $sportEvent->title, 155))

@section('content')
    <x-page-hero
        id="event-title"
        eyebrow="{{ $sportEvent->event_type->label() }}"
        class="content-hero"
    >
        <x-slot:title>
            {{ $sportEvent->title }}
        </x-slot:title>

        <x-slot:description>
            <p>
                {{ $sportEvent->start_at->format('d.m.Y H:i') }} —
                {{ $sportEvent->location_name }}
            </p>
        </x-slot:description>

        <x-slot:actions>
            <a href="{{ route('calendar.index') }}" class="btn btn-secondary">← Wróć do kalendarza</a>
        </x-slot:actions>
    </x-page-hero>

    <section class="features-section event-detail-grid" aria-label="Szczegóły wydarzenia">
        <article class="event-panel">
            <h2>Opis wydarzenia</h2>

            <div class="article-body">
                {!! nl2br(e($sportEvent->description ?: 'Szczegółowy opis nie został jeszcze opublikowany.')) !!}
            </div>

            @if ($sportEvent->competitions->isNotEmpty())
                <h3>Konkurencje</h3>

                <ul class="competition-list">
                    @foreach ($sportEvent->competitions as $competition)
                        <li>
                            <strong>{{ $competition->name }}</strong><br>
                            {{ $competition->competition_system->label() }} /
                            {{ $competition->discipline->label() }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </article>

        <aside class="event-panel">
            <h2>Informacje</h2>

            <dl class="event-facts">
                <div>
                    <dt>Rodzaj</dt>
                    <dd>{{ $sportEvent->event_type->label() }}</dd>
                </div>
                <div>
                    <dt>Termin</dt>
                    <dd>
                        {{ $sportEvent->start_at->format('d.m.Y H:i') }}
                        @if ($sportEvent->end_at)
                            – {{ $sportEvent->end_at->format('d.m.Y H:i') }}
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Miejsce</dt>
                    <dd>
                        {{ $sportEvent->location_name }}
                        @if ($sportEvent->address)
                            <br>{{ $sportEvent->address }}
                        @endif
                    </dd>
                </div>
                @if ($sportEvent->competition_system)
                    <div>
                        <dt>System</dt>
                        <dd>{{ $sportEvent->competition_system->label() }}</dd>
                    </div>
                @endif
                @if ($sportEvent->discipline)
                    <div>
                        <dt>Dyscyplina</dt>
                        <dd>{{ $sportEvent->discipline->label() }}</dd>
                    </div>
                @endif
            </dl>

            @if ($sportEvent->registration_url)
                <div class="btn-group content-actions">
                    <a
                        href="{{ $sportEvent->registration_url }}"
                        class="btn btn-primary"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Przejdź do rejestracji — otwiera w nowej karcie"
                    >
                        Przejdź do rejestracji ↗
                    </a>
                </div>
            @endif
        </aside>
    </section>
@endsection
