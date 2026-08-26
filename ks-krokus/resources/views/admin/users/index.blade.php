@extends('layouts.admin')

@section('title', 'Użytkownicy — panel KS Krokus')
@section('admin_title', 'Użytkownicy')

@section('content')
    <x-admin-page-header
        title="Użytkownicy"
        description="Konta administratorów, moderatorów, użytkowników, trenerów i osób funkcyjnych."
    >
        <x-slot:actions>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Dodaj użytkownika</a>
        </x-slot:actions>
    </x-admin-page-header>

    <form method="GET" class="admin-filter filter-form panel-card">
        <div class="filter-form__row">
        <label>
            Szukaj
            <input id="user-filter-query" type="search" name="q" value="{{ request('q') }}" placeholder="Imię, e-mail lub telefon" autocomplete="off"
                @error('q') aria-invalid="true" aria-describedby="user-filter-query-error" @enderror>
            @error('q') <span id="user-filter-query-error" class="form-error">{{ $message }}</span> @enderror
        </label>

        <label>
            Rola
            <select id="user-filter-role" name="role" @error('role') aria-invalid="true" aria-describedby="user-filter-role-error" @enderror>
                <option value="">Wszystkie</option>
                @foreach ($roles as $value => $label)
                    <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('role') <span id="user-filter-role-error" class="form-error">{{ $message }}</span> @enderror
        </label>

        <label>
            Status konta
            <select id="user-filter-active" name="active" @error('active') aria-invalid="true" aria-describedby="user-filter-active-error" @enderror>
                <option value="">Wszystkie</option>
                <option value="1" @selected(request('active') === '1')>Aktywne</option>
                <option value="0" @selected(request('active') === '0')>Wyłączone</option>
            </select>
            @error('active') <span id="user-filter-active-error" class="form-error">{{ $message }}</span> @enderror
        </label>

            <div class="filter-form__actions">
                <div class="filter-form__action-group">
                    <button type="submit" class="btn btn-primary">Filtruj</button>
                    @if (request()->hasAny(['q', 'role', 'active']))
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Wyczyść</a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    <div class="admin-table-wrap" role="region" aria-label="Lista użytkowników" tabindex="0">
        <table class="admin-table">
            <caption class="sr-only">Lista użytkowników</caption>
            <thead>
                <tr>
                    <th scope="col">Użytkownik</th>
                    <th scope="col">Rola</th>
                    <th scope="col">Telefon</th>
                    <th scope="col">Funkcje</th>
                    <th scope="col">Status</th>
                    <th scope="col">Operacje</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td data-label="Użytkownik">
                            <strong>{{ $user->name }}</strong><br>
                            {{ $user->email }}
                        </td>
                        <td data-label="Rola">{{ $user->role->label() }}</td>
                        <td data-label="Telefon">{{ $user->phone ?: '—' }}</td>
                        <td data-label="Funkcje">
                            @if ($user->is_trainer)
                                <span class="admin-badge admin-badge--success">Trener</span>
                            @endif
                            @if ($user->has_range_access)
                                <span class="admin-badge">Dostęp do strzelnicy</span>
                            @endif
                        </td>
                        <td data-label="Status">
                            <span class="admin-badge {{ $user->is_active ? 'admin-badge--success' : 'admin-badge--danger' }}">
                                {{ $user->is_active ? 'Aktywne' : 'Wyłączone' }}
                            </span>
                        </td>
                        <td data-label="Operacje">
                            <div class="admin-table__actions">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary"
                                    aria-label="Edytuj użytkownika: {{ $user->name }}">Edytuj</a>

                                <a href="{{ route('admin.member-profiles.edit', $user) }}" class="btn btn-secondary"
                                    aria-label="Edytuj dane członkowskie użytkownika: {{ $user->name }}">Dane członkowskie</a>

                                @if (! auth()->user()->is($user))
                                    <form
                                        method="POST"
                                        action="{{ route('admin.users.destroy', $user) }}"
                                        data-confirm="Usunąć użytkownika „{{ $user->name }}”? Konto utraci dostęp, ale historyczne wyniki pozostaną zapisane."
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger-outline"
                                            aria-label="Usuń użytkownika: {{ $user->name }}">Usuń</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                {{ request()->hasAny(['q', 'role', 'active'])
                                    ? 'Brak użytkowników spełniających wybrane kryteria.'
                                    : 'Nie dodano jeszcze żadnego użytkownika.' }}
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
@endsection
