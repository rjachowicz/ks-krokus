@extends('layouts.admin')

@section('title', 'Edycja wydarzenia — panel KS Krokus')
@section('admin_title', 'Edycja wydarzenia')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>{{ $event->title }}</h1>
            <p>{{ $event->start_at->format('d.m.Y H:i') }} — {{ $event->location_name }}</p>
        </div>

        <div class="admin-actions">
            @if ($event->event_type === \App\Enums\EventType::Competition && $event->eventCompetitions()->exists())
                <a
                    href="{{ route('admin.results.create', ['event_competition_id' => $event->eventCompetitions()->value('id')]) }}"
                    class="btn btn-primary"
                >
                    Dodaj wynik
                </a>
            @endif

            @if ($event->is_public && $event->status === \App\Enums\PublicationStatus::Published)
                <a
                    href="{{ route('calendar.show', $event) }}"
                    class="btn btn-secondary"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Podgląd publiczny
                </a>
            @endif
        </div>
    </header>

    <form method="POST" action="{{ route('admin.events.update', $event) }}" class="admin-card admin-form">
        @csrf
        @method('PUT')
        @include('admin.events._form')
    </form>
@endsection
