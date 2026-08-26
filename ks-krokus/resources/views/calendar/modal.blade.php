<article class="event-dialog__event">
    <header class="event-dialog__event-header">
        <span class="category-tag">{{ $sportEvent->event_type->label() }}</span>
        <h2 id="event-dialog-title">{{ $sportEvent->title }}</h2>
        <p id="event-dialog-description">
            Rozpoczęcie {{ $sportEvent->start_at->format('d.m.Y H:i') }}
            @if ($sportEvent->location_name)
                w miejscu {{ $sportEvent->location_name }}.
            @else
                — miejsce nie zostało podane.
            @endif
        </p>
    </header>

    @include('calendar.partials.event-details')

    <footer class="event-dialog__footer">
        <a href="{{ route('calendar.show', $sportEvent) }}" class="btn btn-secondary">
            Otwórz pełny widok
        </a>
    </footer>
</article>
