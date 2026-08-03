@extends('layouts.app')

@section('title', 'Powiadomienia — KS Krokus')

@section('content')
    <x-page-hero id="notifications-page-title" eyebrow="TWOJE KONTO">
        <x-slot:title>Powiadomienia</x-slot:title>
        <x-slot:description>
            <p>Informacje o ogłoszeniach, wnioskach i działaniach wymagających Twojej uwagi.</p>
        </x-slot:description>
    </x-page-hero>

    <section class="page-container page-section page-section--flush notification-center" aria-labelledby="notifications-title">
        <header class="section-header notification-center__header">
            <div>
                <h2 id="notifications-title" class="section-title">Ostatnie powiadomienia</h2>
                <p class="notification-center__summary">
                    Nieprzeczytane: <strong>{{ $unreadCount }}</strong>
                </p>
            </div>

            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-secondary" @disabled($unreadCount === 0)>
                    Oznacz wszystkie jako przeczytane
                </button>
            </form>
        </header>

        @if ($notifications->isEmpty())
            <div class="empty-state">
                <h3>Brak powiadomień</h3>
                <p>Gdy pojawi się nowa informacja dotycząca Twojego konta, zobaczysz ją tutaj.</p>
            </div>
        @else
            <ul class="notification-list" aria-label="Lista powiadomień">
                @foreach ($notifications as $notification)
                    <x-notification-item :notification="$notification" />
                @endforeach
            </ul>

            {{ $notifications->links() }}
        @endif
    </section>
@endsection
