@php
    $selectedCompetitionIds = old(
        'competition_ids',
        isset($event) ? $event->competitions->pluck('id')->all() : [],
    );

    $currentType = old(
        'event_type',
        isset($event) ? $event->event_type->value : \App\Enums\EventType::Competition->value,
    );

    $currentStatus = old(
        'status',
        isset($event) ? $event->status->value : \App\Enums\PublicationStatus::Draft->value,
    );

    $currentDiscipline = old(
        'discipline',
        isset($event) ? $event->discipline?->value : '',
    );

    $currentSystem = old(
        'competition_system',
        isset($event) ? $event->competition_system?->value : '',
    );
@endphp

<div class="admin-form-grid admin-form-grid--3">
    <label class="span-full">
        Nazwa wydarzenia
        <input type="text" name="title" value="{{ old('title', $event->title ?? '') }}" required>
        @error('title') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Rodzaj
        <select name="event_type" required>
            @foreach ($eventTypes as $value => $label)
                <option value="{{ $value }}" @selected($currentType === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('event_type') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Status publikacji
        <select name="status" required>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected($currentStatus === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="admin-check-option">
        <input type="hidden" name="is_public" value="0">
        <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $event->is_public ?? true))>
        <span>
            <strong>Wydarzenie publiczne</strong><br>
            Widoczne w kalendarzu strony.
        </span>
    </label>

    <label>
        Początek
        <input
            type="datetime-local"
            name="start_at"
            value="{{ old('start_at', isset($event) ? $event->start_at->format('Y-m-d\TH:i') : '') }}"
            required
        >
        @error('start_at') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Koniec
        <input
            type="datetime-local"
            name="end_at"
            value="{{ old('end_at', isset($event) && $event->end_at ? $event->end_at->format('Y-m-d\TH:i') : '') }}"
        >
        @error('end_at') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Link do rejestracji
        <input
            type="url"
            name="registration_url"
            value="{{ old('registration_url', $event->registration_url ?? '') }}"
            placeholder="https://..."
        >
        @error('registration_url') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Nazwa miejsca
        <input type="text" name="location_name" value="{{ old('location_name', $event->location_name ?? '') }}" required>
        @error('location_name') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="span-2">
        Adres
        <input type="text" name="address" value="{{ old('address', $event->address ?? '') }}">
        @error('address') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Główna dyscyplina
        <select name="discipline">
            <option value="">Wydarzenie mieszane</option>
            @foreach ($disciplines as $value => $label)
                <option value="{{ $value }}" @selected($currentDiscipline === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('discipline') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label>
        Główny system
        <select name="competition_system">
            <option value="">Wydarzenie mieszane</option>
            @foreach ($systems as $value => $label)
                <option value="{{ $value }}" @selected($currentSystem === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('competition_system') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <label class="span-full">
        Opis
        <textarea name="description" rows="10">{{ old('description', $event->description ?? '') }}</textarea>
        @error('description') <span class="form-error">{{ $message }}</span> @enderror
    </label>

    <div class="span-full">
        <h3 class="admin-section-title">Konkurencje wydarzenia</h3>
        <p class="form-help">
            Dla treningów możesz wybrać ćwiczone konkurencje. Dla zawodów wybór definiuje pozycje dostępne
            przy dodawaniu wyników.
        </p>
    </div>

    <div class="admin-check-grid span-full">
        @foreach ($competitionDefinitions as $group => $definitions)
            <div class="admin-card">
                <h4>{{ $group }}</h4>

                @foreach ($definitions as $definition)
                    <label class="admin-check-option">
                        <input
                            type="checkbox"
                            name="competition_ids[]"
                            value="{{ $definition->id }}"
                            @checked(in_array($definition->id, array_map('intval', $selectedCompetitionIds), true))
                        >
                        <span>
                            <strong>{{ $definition->name }}</strong><br>
                            <code>{{ $definition->code }}</code>
                        </span>
                    </label>
                @endforeach
            </div>
        @endforeach
    </div>
</div>

<div class="admin-actions">
    <button type="submit" class="btn btn-primary">Zapisz wydarzenie</button>
    <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">Anuluj</a>
</div>
