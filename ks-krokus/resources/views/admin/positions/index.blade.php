@extends('layouts.admin')

@section('title', 'Funkcje klubowe — panel KS Krokus')
@section('admin_title', 'Funkcje klubowe')

@section('content')
    <x-admin-page-header
        title="Funkcje klubowe"
        description="Przypisywanie użytkowników do ról takich jak prezes, skarbnik, sekretarz i komisja rewizyjna."
    >
        <x-slot:actions>
            <a href="{{ route('admin.positions.create') }}" class="btn btn-primary">Dodaj funkcję</a>
        </x-slot:actions>
    </x-admin-page-header>

    <div class="admin-table-wrap" role="region" aria-label="Lista funkcji klubowych" tabindex="0">
        <table class="admin-table">
            <caption class="sr-only">Lista funkcji klubowych</caption>
            <thead>
                <tr>
                    <th scope="col">Kolejność</th>
                    <th scope="col">Funkcja</th>
                    <th scope="col">Przypisane osoby</th>
                    <th scope="col">Status</th>
                    <th scope="col">Operacje</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($positions as $position)
                    <tr>
                        <td data-label="Kolejność">{{ $position->sort_order }}</td>
                        <td data-label="Funkcja">
                            <strong>{{ $position->name }}</strong><br>
                            <code>{{ $position->slug }}</code>
                        </td>
                        <td data-label="Przypisane osoby">
                            @forelse ($position->users as $user)
                                <span class="admin-badge">{{ $user->name }}</span>
                            @empty
                                —
                            @endforelse
                        </td>
                        <td data-label="Status">
                            <span class="admin-badge {{ $position->is_active ? 'admin-badge--success' : 'admin-badge--danger' }}">
                                {{ $position->is_active ? 'Aktywna' : 'Wyłączona' }}
                            </span>
                        </td>
                        <td data-label="Operacje">
                            <div class="admin-table__actions">
                                <a href="{{ route('admin.positions.edit', $position) }}" class="btn btn-secondary"
                                    aria-label="Edytuj funkcję klubową: {{ $position->name }}">Edytuj</a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.positions.destroy', $position) }}"
                                    data-confirm="Usunąć funkcję klubową „{{ $position->name }}”? Zniknie ze strony kontaktowej. Tej operacji nie można cofnąć."
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-outline"
                                        aria-label="Usuń funkcję klubową: {{ $position->name }}">Usuń</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Nie dodano jeszcze żadnej funkcji klubowej.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $positions->links() }}
@endsection
