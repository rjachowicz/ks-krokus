@extends('layouts.admin')

@section('title', 'Edycja wydarzenia — panel KS Krokus')
@section('admin_title', 'Edycja wydarzenia')

@section('content')
    <x-admin-page-header
        :title="$event->title"
        :description="$event->start_at->format('d.m.Y H:i').' — '.$event->location_name"
    >
        <x-slot:actions>
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
        </x-slot:actions>
    </x-admin-page-header>

    <form method="POST" action="{{ route('admin.events.update', $event) }}" class="admin-card admin-form">
        @csrf
        @method('PUT')
        @include('admin.events._form')
    </form>
@endsection
