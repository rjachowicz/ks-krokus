@extends('layouts.admin')

@section('title', 'Użytkownicy — panel KS Krokus')
@section('admin_title', 'Użytkownicy')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>Użytkownicy</h1>
            <p>Konta administratorów, moderatorów, użytkowników, trenerów i osób funkcyjnych.</p>
        </div>

        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Dodaj użytkownika</a>
    </header>

    <form method="GET" class="admin-filter">
        <label>
            Szukaj
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Imię, e-mail lub telefon">
        </label>

        <label>
            Rola
            <select name="role">
                <option value="">Wszystkie</option>
                @foreach ($roles as $value => $label)
                    <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label>
            Status konta
            <select name="active">
                <option value="">Wszystkie</option>
                <option value="1" @selected(request('active') === '1')>Aktywne</option>
                <option value="0" @selected(request('active') === '0')>Wyłączone</option>
            </select>
        </label>

        <button type="submit" class="btn btn-primary">Filtruj</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Wyczyść</a>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Użytkownik</th>
                    <th>Rola</th>
                    <th>Telefon</th>
                    <th>Funkcje</th>
                    <th>Status</th>
                    <th>Operacje</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <strong>{{ $user->name }}</strong><br>
                            {{ $user->email }}
                        </td>
                        <td>{{ $user->role->label() }}</td>
                        <td>{{ $user->phone ?: '—' }}</td>
                        <td>
                            @if ($user->is_trainer)
                                <span class="admin-badge admin-badge--success">Trener</span>
                            @endif
                            @if ($user->has_range_access)
                                <span class="admin-badge">Dostęp do strzelnicy</span>
                            @endif
                        </td>
                        <td>
                            <span class="admin-badge {{ $user->is_active ? 'admin-badge--success' : 'admin-badge--danger' }}">
                                {{ $user->is_active ? 'Aktywne' : 'Wyłączone' }}
                            </span>
                        </td>
                        <td>
                            <div class="admin-table__actions">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary">Edytuj</a>

                                @if (! auth()->user()->is($user))
                                    <form
                                        method="POST"
                                        action="{{ route('admin.users.destroy', $user) }}"
                                        onsubmit="return confirm('Usunąć użytkownika?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary">Usuń</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Brak użytkowników spełniających kryteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
@endsection
