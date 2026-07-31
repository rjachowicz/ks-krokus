@extends('layouts.admin')

@section('title', 'Konkurencje — panel KS Krokus')
@section('admin_title', 'Konkurencje')

@section('content')
    <header class="admin-page-header">
        <div>
            <h1>Słownik konkurencji</h1>
            <p>Konkurencje ISSF i IPSC używane w wydarzeniach oraz wynikach.</p>
        </div>

        <a href="{{ route('admin.competitions.create') }}" class="btn btn-primary">Dodaj konkurencję</a>
    </header>

    <form method="GET" class="admin-filter">
        <label>
            System
            <select id="competition-filter-system" name="competition_system" @error('competition_system') aria-invalid="true" aria-describedby="competition-filter-system-error" @enderror>
                <option value="">Wszystkie</option>
                @foreach ($systems as $value => $label)
                    <option value="{{ $value }}" @selected(request('competition_system') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('competition_system') <span id="competition-filter-system-error" class="form-error">{{ $message }}</span> @enderror
        </label>

        <label>
            Dyscyplina
            <select id="competition-filter-discipline" name="discipline" @error('discipline') aria-invalid="true" aria-describedby="competition-filter-discipline-error" @enderror>
                <option value="">Wszystkie</option>
                @foreach ($disciplines as $value => $label)
                    <option value="{{ $value }}" @selected(request('discipline') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('discipline') <span id="competition-filter-discipline-error" class="form-error">{{ $message }}</span> @enderror
        </label>

        <button type="submit" class="btn btn-primary">Filtruj</button>
        <a href="{{ route('admin.competitions.index') }}" class="btn btn-secondary">Wyczyść</a>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Kod</th>
                    <th>Nazwa</th>
                    <th>System</th>
                    <th>Dyscyplina</th>
                    <th>Status</th>
                    <th>Operacje</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($definitions as $definition)
                    <tr>
                        <td><code>{{ $definition->code }}</code></td>
                        <td>{{ $definition->name }}</td>
                        <td>{{ $definition->competition_system->label() }}</td>
                        <td>{{ $definition->discipline->label() }}</td>
                        <td>
                            <span class="admin-badge {{ $definition->is_active ? 'admin-badge--success' : 'admin-badge--danger' }}">
                                {{ $definition->is_active ? 'Aktywna' : 'Wyłączona' }}
                            </span>
                        </td>
                        <td>
                            <div class="admin-table__actions">
                                <a href="{{ route('admin.competitions.edit', $definition) }}" class="btn btn-secondary">Edytuj</a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.competitions.destroy', $definition) }}"
                                    data-confirm="Usunąć konkurencję? Tej operacji nie można cofnąć."
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-outline">Usuń</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Brak konkurencji.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $definitions->links() }}
@endsection
