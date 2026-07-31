@extends('layouts.admin')

@section('title', 'Konkurencje — panel KS Krokus')
@section('admin_title', 'Konkurencje')

@section('content')
    <x-admin-page-header
        title="Słownik konkurencji"
        description="Konkurencje ISSF i IPSC używane w wydarzeniach oraz wynikach."
    >
        <x-slot:actions>
            <a href="{{ route('admin.competitions.create') }}" class="btn btn-primary">Dodaj konkurencję</a>
        </x-slot:actions>
    </x-admin-page-header>

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
        @if (request()->hasAny(['competition_system', 'discipline']))
            <a href="{{ route('admin.competitions.index') }}" class="btn btn-secondary">Wyczyść</a>
        @endif
    </form>

    <div class="admin-table-wrap" role="region" aria-label="Lista konkurencji" tabindex="0">
        <table class="admin-table">
            <caption class="sr-only">Lista konkurencji</caption>
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
                        <td data-label="Kod"><code>{{ $definition->code }}</code></td>
                        <td data-label="Nazwa">{{ $definition->name }}</td>
                        <td data-label="System">{{ $definition->competition_system->label() }}</td>
                        <td data-label="Dyscyplina">{{ $definition->discipline->label() }}</td>
                        <td data-label="Status">
                            <span class="admin-badge {{ $definition->is_active ? 'admin-badge--success' : 'admin-badge--danger' }}">
                                {{ $definition->is_active ? 'Aktywna' : 'Wyłączona' }}
                            </span>
                        </td>
                        <td data-label="Operacje">
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
