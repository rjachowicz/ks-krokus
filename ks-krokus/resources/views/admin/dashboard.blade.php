@extends('layouts.admin')

@section('title', 'Pulpit — panel KS Krokus')
@section('admin_title', 'Pulpit')

@section('content')
    <x-admin-page-header
        :title="auth()->user()->canManageContent() ? 'Panel administracyjny' : 'Strefa użytkownika'"
        :description="auth()->user()->canManageContent()
            ? 'Zarządzanie treściami, kalendarzem i wynikami.'
            : 'Twoje wyniki oraz najbliższe publiczne wydarzenia klubu.'"
    >
        @if (auth()->user()->canManageContent())
            <x-slot:actions>
                <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">Dodaj aktualność</a>
                <a href="{{ route('admin.events.create') }}" class="btn btn-secondary">Dodaj wydarzenie</a>
                <a href="{{ route('admin.results.create') }}" class="btn btn-secondary">Dodaj wynik</a>
            </x-slot:actions>
        @endif
    </x-admin-page-header>

    @if ($metrics !== null)
        <section class="admin-metrics ui-grid" aria-label="Statystyki panelu">
            @if ($metrics['users'] !== null)
                <div class="admin-metric">
                    <span class="admin-metric__value">{{ $metrics['users'] }}</span>
                    <span class="admin-metric__label">Użytkownicy</span>
                </div>
            @endif
            @if ($metrics['pending_account_requests'] !== null)
                <a class="admin-metric" href="{{ route('admin.account-requests.index', ['status' => 'pending']) }}">
                    <span class="admin-metric__value">{{ $metrics['pending_account_requests'] }}</span>
                    <span class="admin-metric__label">Wnioski o konto</span>
                </a>
            @endif
            <div class="admin-metric">
                <span class="admin-metric__value">{{ $metrics['posts'] }}</span>
                <span class="admin-metric__label">Aktualności</span>
            </div>
            <div class="admin-metric">
                <span class="admin-metric__value">{{ $metrics['published_posts'] }}</span>
                <span class="admin-metric__label">Opublikowane</span>
            </div>
            <div class="admin-metric">
                <span class="admin-metric__value">{{ $metrics['events'] }}</span>
                <span class="admin-metric__label">Wydarzenia</span>
            </div>
            <div class="admin-metric">
                <span class="admin-metric__value">{{ $metrics['results'] }}</span>
                <span class="admin-metric__label">Wyniki</span>
            </div>
        </section>
    @endif

    <section class="panel-card">
        <h2>Najbliższe wydarzenia</h2>

        @if ($upcomingEvents->isEmpty())
            <p class="empty-state">Brak zaplanowanych wydarzeń.</p>
        @else
            <div class="admin-table-wrap" role="region" aria-label="Najbliższe wydarzenia" tabindex="0">
                <table class="admin-table">
                    <caption class="sr-only">Najbliższe wydarzenia</caption>
                    <thead>
                        <tr>
                            <th scope="col">Termin</th>
                            <th scope="col">Nazwa</th>
                            <th scope="col">Rodzaj</th>
                            <th scope="col">Miejsce</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($upcomingEvents as $event)
                            <tr>
                                <td data-label="Termin">{{ $event->start_at->format('d.m.Y H:i') }}</td>
                                <td data-label="Nazwa">
                                    @if (auth()->user()->canManageContent())
                                        <a href="{{ route('admin.events.edit', $event) }}">{{ $event->title }}</a>
                                    @else
                                        {{ $event->title }}
                                    @endif
                                </td>
                                <td data-label="Rodzaj">{{ $event->event_type->label() }}</td>
                                <td data-label="Miejsce">{{ $event->location_name }}</td>
                                <td data-label="Status">{{ $event->status->label() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="panel-card">
        <h2>Powiadomienia</h2>
        @if ($notifications->isEmpty())
            <p class="empty-state">Nie masz nowych informacji dotyczących ogłoszeń.</p>
        @else
            <ul class="dashboard-notifications" aria-label="Ostatnie powiadomienia">
                @foreach ($notifications as $notification)
                    <x-notification-item :notification="$notification" compact />
                @endforeach
            </ul>
            <p><a href="{{ route('notifications.index') }}" class="btn btn-secondary">Zobacz wszystkie powiadomienia</a></p>
        @endif
    </section>

    <section class="panel-card">
        <h2>Moje wyniki</h2>

        @if ($ownResults->isEmpty())
            <p class="empty-state">Do Twojego konta nie przypisano jeszcze wyników.</p>
        @else
            <div class="admin-table-wrap" role="region" aria-label="Moje wyniki" tabindex="0">
                <table class="admin-table">
                    <caption class="sr-only">Moje wyniki</caption>
                    <thead>
                        <tr>
                            <th scope="col">Wydarzenie</th>
                            <th scope="col">Konkurencja</th>
                            <th scope="col">Wynik</th>
                            <th scope="col">Miejsce</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ownResults as $result)
                            <tr>
                                <td data-label="Wydarzenie">{{ $result->eventCompetition->event->title }}</td>
                                <td data-label="Konkurencja">{{ $result->eventCompetition->competition->name }}</td>
                                <td data-label="Wynik">{{ $result->score }}</td>
                                <td data-label="Miejsce">{{ $result->place ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
