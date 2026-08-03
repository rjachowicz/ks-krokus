@props(['notification', 'compact' => false])

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
    </div>
</li>
