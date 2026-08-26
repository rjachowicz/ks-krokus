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

    <section class="page-container page-section" aria-label="Szczegóły wydarzenia">
        <x-form-errors />
        @include('calendar.partials.event-details')
    </section>
@endsection
