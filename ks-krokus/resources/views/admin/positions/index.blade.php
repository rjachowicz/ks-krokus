@extends('layouts.admin')

@section('title', 'Funkcje klubowe — panel KS Krokus')
@section('admin_title', 'Funkcje klubowe')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>Funkcje klubowe</h1>
            <p>Przypisywanie użytkowników do ról takich jak prezes, skarbnik, sekretarz i komisja rewizyjna.</p>
        </div>

        <a href="{{ route('admin.positions.create') }}" class="btn btn-primary">Dodaj funkcję</a>
    </header>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Kolejność</th>
                    <th>Funkcja</th>
                    <th>Przypisane osoby</th>
                    <th>Status</th>
                    <th>Operacje</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($positions as $position)
                    <tr>
                        <td>{{ $position->sort_order }}</td>
                        <td>
                            <strong>{{ $position->name }}</strong><br>
                            <code>{{ $position->slug }}</code>
                        </td>
                        <td>
                            @forelse ($position->users as $user)
                                <span class="admin-badge">{{ $user->name }}</span>
                            @empty
                                —
                            @endforelse
                        </td>
                        <td>
                            <span class="admin-badge {{ $position->is_active ? 'admin-badge--success' : 'admin-badge--danger' }}">
                                {{ $position->is_active ? 'Aktywna' : 'Wyłączona' }}
                            </span>
                        </td>
                        <td>
                            <div class="admin-table__actions">
                                <a href="{{ route('admin.positions.edit', $position) }}" class="btn btn-secondary">Edytuj</a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.positions.destroy', $position) }}"
                                    data-confirm="Usunąć funkcję klubową? Tej operacji nie można cofnąć."
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-outline">Usuń</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Brak funkcji klubowych.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $positions->links() }}
@endsection
