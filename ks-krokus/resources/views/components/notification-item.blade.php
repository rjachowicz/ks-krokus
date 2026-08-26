@props([
    'notification',
    'compact' => false,
    'selectable' => false,
    'selectionForm' => null,
    'returnPage' => 1,
    'selectedNotifications' => [],
])

@php
    $notificationUrl = $notification->data['url'] ?? route('notifications.index');
    $notificationUrl = is_string($notificationUrl) ? $notificationUrl : route('notifications.index');
    $applicationHost = parse_url((string) config('app.url'), PHP_URL_HOST);
    $notificationHost = parse_url($notificationUrl, PHP_URL_HOST);
    $isInternalUrl = (str_starts_with($notificationUrl, '/') && ! str_starts_with($notificationUrl, '//'))
        || ($applicationHost !== null && $notificationHost === $applicationHost);

    if (! $isInternalUrl) {
        $notificationUrl = route('notifications.index');
    }
@endphp

<li class="notification-item {{ $notification->unread() ? 'notification-item--unread' : '' }}">
    <div class="notification-item__main">
        @if ($selectable && is_string($selectionForm))
            <label class="form-check notification-item__selection">
                <input
                    type="checkbox"
                    name="notifications[]"
                    value="{{ $notification->getKey() }}"
                    form="{{ $selectionForm }}"
                    data-notification-select
                    @checked(in_array($notification->getKey(), $selectedNotifications, true))
                >
                <span class="sr-only">
                    Zaznacz powiadomienie: {{ $notification->data['title'] ?? 'Powiadomienie' }}
                </span>
            </label>
        @endif

        <div class="notification-item__content">
            <div class="notification-item__heading">
                <strong>{{ $notification->data['title'] ?? 'Powiadomienie' }}</strong>
                <span class="notification-item__status">
                    {{ $notification->unread() ? 'Nowe' : 'Przeczytane' }}
                </span>
            </div>

            @if (filled($notification->data['message'] ?? null))
                <p>{{ $notification->data['message'] }}</p>
            @endif

            <time datetime="{{ $notification->created_at->toIso8601String() }}">
                {{ $notification->created_at->diffForHumans() }}
            </time>
        </div>
    </div>

    <div class="notification-item__actions">
        <a href="{{ $notificationUrl }}" class="btn btn-secondary">
            {{ $compact ? 'Otwórz' : 'Przejdź do informacji' }}
        </a>

        @if ($notification->unread())
            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-secondary">Oznacz jako przeczytane</button>
            </form>
        @endif

        @if (! $compact)
            <form
                method="POST"
                action="{{ route('notifications.destroy', ['notification' => $notification->getKey(), 'page' => $returnPage]) }}"
                data-confirm="Usunąć to powiadomienie? Tej operacji nie można cofnąć."
                data-confirm-action="Usuń powiadomienie"
            >
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger-outline">Usuń</button>
            </form>
        @endif
    </div>
</li>
