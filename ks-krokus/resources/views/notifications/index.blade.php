@extends('layouts.app')

@section('title', 'Powiadomienia — KS Krokus')

@section('content')
    @php
        $oldSelectedNotifications = old('notifications', []);
        $selectedNotifications = is_array($oldSelectedNotifications) ? $oldSelectedNotifications : [];
    @endphp

    <x-page-hero id="notifications-page-title" eyebrow="TWOJE KONTO">
        <x-slot:title>Powiadomienia</x-slot:title>
        <x-slot:description>
            <p>Informacje o ogłoszeniach, wnioskach i działaniach wymagających Twojej uwagi.</p>
        </x-slot:description>
    </x-page-hero>

    <section class="page-container page-section page-section--flush notification-center" aria-labelledby="notifications-title">
        <x-form-errors />

        <header class="section-header notification-center__header">
            <div>
                <h2 id="notifications-title" class="section-title">Ostatnie powiadomienia</h2>
                <p class="notification-center__summary">
                    Nieprzeczytane: <strong>{{ $unreadCount }}</strong>
                </p>
            </div>

            <div class="notification-center__header-actions">
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary" @disabled($unreadCount === 0)>
                        Oznacz wszystkie jako przeczytane
                    </button>
                </form>

                <form
                    method="POST"
                    action="{{ route('notifications.destroy-all') }}"
                    data-confirm="Usunąć wszystkie Twoje powiadomienia? Tej operacji nie można cofnąć."
                    data-confirm-action="Usuń wszystkie"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger-outline" @disabled($notifications->total() === 0)>
                        Usuń wszystkie
                    </button>
                </form>
            </div>
        </header>

        @if ($notifications->isEmpty())
            <div class="empty-state">
                <h3>Brak powiadomień</h3>
                <p>Gdy pojawi się nowa informacja dotycząca Twojego konta, zobaczysz ją tutaj.</p>
            </div>
        @else
            <form
                id="notification-bulk-delete"
                class="notification-bulk-actions"
                method="POST"
                action="{{ route('notifications.destroy-selected', ['page' => $notifications->currentPage()]) }}"
                data-notification-selection
                data-confirm="Usunąć zaznaczone powiadomienia? Tej operacji nie można cofnąć."
                data-confirm-action="Usuń zaznaczone"
            >
                @csrf
                @method('DELETE')

                @foreach ($notifications as $notification)
                    <input type="hidden" name="visible_notifications[]" value="{{ $notification->getKey() }}">
                @endforeach

                <label class="form-check notification-bulk-actions__select-all">
                    <input
                        type="checkbox"
                        name="select_visible"
                        value="1"
                        data-notification-select-visible
                        @checked(old('select_visible'))
                        @if ($errors->has('notifications') || $errors->has('notifications.*'))
                            aria-invalid="true"
                            aria-describedby="notification-selection-error"
                        @endif
                    >
                    <span>Zaznacz wszystkie widoczne</span>
                </label>

                <span class="notification-bulk-actions__counter" aria-live="polite" data-notification-selected-count>
                    Zaznaczono: {{ count($selectedNotifications) }}
                </span>

                <button type="submit" class="btn btn-danger-outline" data-notification-delete-selected>
                    Usuń zaznaczone
                </button>

                @if ($errors->has('notifications') || $errors->has('notifications.*'))
                    <p id="notification-selection-error" class="form-error">
                        {{ $errors->first('notifications') ?: $errors->first('notifications.*') }}
                    </p>
                @endif
            </form>

            <ul class="notification-list" aria-label="Lista powiadomień">
                @foreach ($notifications as $notification)
                    <x-notification-item
                        :notification="$notification"
                        :selectable="true"
                        selection-form="notification-bulk-delete"
                        :return-page="$notifications->currentPage()"
                        :selected-notifications="$selectedNotifications"
                    />
                @endforeach
            </ul>

            {{ $notifications->links() }}
        @endif
    </section>
@endsection
