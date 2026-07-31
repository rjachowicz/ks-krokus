@extends('layouts.admin')

@section('title', 'Pulpit — panel KS Krokus')
@section('admin_title', 'Pulpit')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>{{ auth()->user()->canManageContent() ? 'Panel administracyjny' : 'Strefa użytkownika' }}</h1>
            <p>
                {{ auth()->user()->canManageContent()
                    ? 'Zarządzanie treściami, kalendarzem i wynikami.'
                    : 'Twoje wyniki oraz najbliższe publiczne wydarzenia klubu.' }}
            </p>
        </div>

        @if (auth()->user()->canManageContent())
            <div class="admin-actions">
                <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">Dodaj aktualność</a>
                <a href="{{ route('admin.events.create') }}" class="btn btn-secondary">Dodaj wydarzenie</a>
                <a href="{{ route('admin.results.create') }}" class="btn btn-secondary">Dodaj wynik</a>
            </div>
        @endif
    </header>

    @if ($metrics !== null)
        <section class="admin-metrics" aria-label="Statystyki panelu">
            @if ($metrics['users'] !== null)
                <article class="admin-metric">
                    <span class="admin-metric__value">{{ $metrics['users'] }}</span>
                    <span class="admin-metric__label">Użytkownicy</span>
                </article>
            @endif
            <article class="admin-metric">
                <span class="admin-metric__value">{{ $metrics['posts'] }}</span>
                <span class="admin-metric__label">Aktualności</span>
            </article>
            <article class="admin-metric">
                <span class="admin-metric__value">{{ $metrics['published_posts'] }}</span>
                <span class="admin-metric__label">Opublikowane</span>
            </article>
            <article class="admin-metric">
                <span class="admin-metric__value">{{ $metrics['events'] }}</span>
                <span class="admin-metric__label">Wydarzenia</span>
            </article>
            <article class="admin-metric">
                <span class="admin-metric__value">{{ $metrics['results'] }}</span>
                <span class="admin-metric__label">Wyniki</span>
            </article>
        </section>
    @endif

    <section class="admin-card">
        <h2>Najbliższe wydarzenia</h2>

        @if ($upcomingEvents->isEmpty())
            <p>Brak zaplanowanych wydarzeń.</p>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Termin</th>
                            <th>Nazwa</th>
                            <th>Rodzaj</th>
                            <th>Miejsce</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($upcomingEvents as $event)
                            <tr>
                                <td>{{ $event->start_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    @if (auth()->user()->canManageContent())
                                        <a href="{{ route('admin.events.edit', $event) }}">{{ $event->title }}</a>
                                    @else
                                        {{ $event->title }}
                                    @endif
                                </td>
                                <td>{{ $event->event_type->label() }}</td>
                                <td>{{ $event->location_name }}</td>
                                <td>{{ $event->status->label() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="admin-card">
        <h2>Moje wyniki</h2>

        @if ($ownResults->isEmpty())
            <p>Do Twojego konta nie przypisano jeszcze wyników.</p>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Wydarzenie</th>
                            <th>Konkurencja</th>
                            <th>Wynik</th>
                            <th>Miejsce</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ownResults as $result)
                            <tr>
                                <td>{{ $result->eventCompetition->event->title }}</td>
                                <td>{{ $result->eventCompetition->competition->name }}</td>
                                <td>{{ $result->score }}</td>
                                <td>{{ $result->place ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
